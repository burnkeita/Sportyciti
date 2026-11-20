<?php

namespace App\Services\SportsData;

use App\Enums\FixtureStatus;
use App\Models\Competition;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Standing;
use App\Models\Team;
use App\Services\SportsData\Contracts\FootballDataProvider;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Persists normalised provider data. Only competitions configured (and active)
 * in the admin panel are stored; everything else returned by the provider is ignored.
 */
class FootballSyncService
{
    /** @var array<string, Team> */
    private array $teamCache = [];

    public function __construct(private readonly FootballDataProvider $provider) {}

    public function provider(): FootballDataProvider
    {
        return $this->provider;
    }

    /** Competitions this provider should sync, keyed by external id. */
    public function competitions(): Collection
    {
        return Competition::query()->syncable()->where('provider', $this->provider->key())->get()->keyBy('external_id');
    }

    public function syncDate(CarbonInterface $date): int
    {
        return $this->persistFixtures($this->provider->fixturesByDate($date));
    }

    /**
     * Sync in-play fixtures plus any we still think are live (to capture full-time).
     * Fetches full details (events/lineups/stats) for those fixtures.
     */
    public function syncLive(): int
    {
        $live = $this->provider->liveFixtures();
        $count = $this->persistFixtures($live);

        $ids = collect($live)->pluck('external_id')
            ->merge(Fixture::query()->where('provider', $this->provider->key())->live()->pluck('external_id'))
            ->unique()->values();

        foreach ($ids->chunk(20) as $chunk) {
            $this->persistFixtures($this->provider->fixtureDetails($chunk->all()), withDetails: true);
        }

        Cache::forget('football.live_count');

        return $count;
    }

    public function syncFixtureDetails(Fixture $fixture): bool
    {
        $rows = $this->provider->fixtureDetails([$fixture->external_id]);
        $this->persistFixtures($rows, withDetails: true);

        return $rows !== [];
    }

    public function syncSeason(Competition $competition): int
    {
        $count = $this->persistFixtures($this->provider->seasonFixtures($competition->external_id, $competition->current_season_year));
        $competition->forceFill(['last_synced_at' => now()])->saveQuietly();

        return $count;
    }

    public function syncStandings(Competition $competition): int
    {
        if (! $competition->has_standings) {
            return 0;
        }

        $rows = $this->provider->standings($competition->external_id, $competition->current_season_year);
        $season = $this->season($competition, $competition->current_season_year);

        DB::transaction(function () use ($rows, $season, $competition): void {
            $keep = [];

            foreach ($rows as $row) {
                $team = $this->team($row['team'], $competition);
                $standing = Standing::updateOrCreate(
                    ['season_id' => $season->id, 'group_name' => (string) $row['group'], 'team_id' => $team->id],
                    collect($row)->only(['rank', 'played', 'won', 'drawn', 'lost', 'goals_for', 'goals_against', 'goal_difference', 'points', 'form', 'description', 'home', 'away'])->all(),
                );
                $keep[] = $standing->id;
            }

            if ($rows !== []) {
                Standing::where('season_id', $season->id)->whereNotIn('id', $keep)->delete();
            }
        });

        Cache::forget("football.standings.{$season->id}");

        return count($rows);
    }

    /** Head-to-head is cached rather than persisted (it spans competitions we may not store). */
    public function headToHead(Fixture $fixture, int $last = 10): array
    {
        $home = $fixture->homeTeam;
        $away = $fixture->awayTeam;

        if (! $home?->external_id || ! $away?->external_id || $home->provider !== $this->provider->key()) {
            return [];
        }

        return Cache::remember(
            "football.h2h.{$this->provider->key()}.{$home->external_id}.{$away->external_id}.{$last}",
            config('sportsdata.cache.head_to_head'),
            fn () => $this->provider->headToHead($home->external_id, $away->external_id, $last),
        );
    }

    // ---------------------------------------------------------------------

    /** @param list<array> $rows */
    public function persistFixtures(array $rows, bool $withDetails = false): int
    {
        $competitions = $this->competitions();
        $count = 0;

        foreach ($rows as $row) {
            $competition = $competitions->get($row['league']['external_id'] ?? '');

            if (! $competition) {
                continue; // not a competition SportyCiti covers
            }

            DB::transaction(function () use ($row, $competition, $withDetails): void {
                $home = $this->team($row['home'], $competition);
                $away = $this->team($row['away'], $competition);
                $seasonYear = $row['league']['season'] ?? $competition->current_season_year;

                $fixture = Fixture::updateOrCreate(
                    ['provider' => $this->provider->key(), 'external_id' => $row['external_id']],
                    [
                        'competition_id' => $competition->id,
                        'season_id' => $seasonYear ? $this->season($competition, (int) $seasonYear)->id : null,
                        'home_team_id' => $home->id,
                        'away_team_id' => $away->id,
                        'kickoff_at' => Carbon::parse($row['kickoff_at'])->utc(),
                        'status' => $row['status'],
                        'status_long' => $row['status_long'] ?? FixtureStatus::label($row['status']),
                        'elapsed' => $row['elapsed'],
                        'home_score' => $row['score']['home'],
                        'away_score' => $row['score']['away'],
                        'home_ht_score' => $row['score']['ht_home'],
                        'away_ht_score' => $row['score']['ht_away'],
                        'home_pen_score' => $row['score']['pen_home'],
                        'away_pen_score' => $row['score']['pen_away'],
                        'round' => $row['league']['round'] ?? null,
                        'venue_name' => $row['venue_name'],
                        'venue_city' => $row['venue_city'],
                        'referee' => $row['referee'],
                    ],
                );

                if ($withDetails) {
                    $this->persistDetails($fixture, $row, $competition);
                }
            });

            $count++;
        }

        return $count;
    }

    private function persistDetails(Fixture $fixture, array $row, Competition $competition): void
    {
        $teamId = fn (?string $externalId) => $externalId === null ? null : match ($externalId) {
            $row['home']['external_id'] => $fixture->home_team_id,
            $row['away']['external_id'] => $fixture->away_team_id,
            default => null,
        };

        if (is_array($row['events'])) {
            $fixture->events()->delete();
            foreach (array_values($row['events']) as $i => $event) {
                $fixture->events()->create([
                    'team_id' => $teamId($event['team_external_id']),
                    'minute' => $event['minute'],
                    'extra_minute' => $event['extra_minute'],
                    'type' => $event['type'],
                    'detail' => $event['detail'],
                    'player_name' => $event['player'],
                    'assist_name' => $event['assist'],
                    'comments' => $event['comments'],
                    'sort_order' => $i,
                ]);
            }
        }

        if (is_array($row['lineups'])) {
            foreach ($row['lineups'] as $lineup) {
                $team = $teamId($lineup['team_external_id']);
                if (! $team) {
                    continue;
                }
                $fixture->lineups()->updateOrCreate(['team_id' => $team], [
                    'formation' => $lineup['formation'],
                    'coach_name' => $lineup['coach'],
                    'starting_xi' => $lineup['starting_xi'],
                    'substitutes' => $lineup['substitutes'],
                ]);
            }
        }

        if (is_array($row['statistics'])) {
            foreach ($row['statistics'] as $teamStats) {
                $team = $teamId($teamStats['team_external_id']);
                if (! $team) {
                    continue;
                }
                foreach ($teamStats['statistics'] as $stat) {
                    $fixture->statistics()->updateOrCreate(['team_id' => $team, 'type' => $stat['type']], ['value' => $stat['value']]);
                }
            }
        }

        $fixture->forceFill(['details_synced_at' => now()])->saveQuietly();
    }

    private function team(array $data, Competition $competition): Team
    {
        $key = $this->provider->key().':'.$data['external_id'];

        if (isset($this->teamCache[$key])) {
            return $this->teamCache[$key];
        }

        $team = Team::firstOrNew(['provider' => $this->provider->key(), 'external_id' => $data['external_id']]);
        $team->name = $data['name'];
        $team->logo_url = $data['logo'] ?? $team->logo_url;
        $team->country_id ??= $competition->country_id;
        if ($team->isDirty()) {
            $team->save();
        }

        $competition->teams()->syncWithoutDetaching([$team->id]);

        return $this->teamCache[$key] = $team;
    }

    private function season(Competition $competition, int $year): Season
    {
        return Season::firstOrCreate(
            ['competition_id' => $competition->id, 'year' => $year],
            ['name' => Season::labelFor($year), 'is_current' => $year === (int) $competition->current_season_year],
        );
    }
}
