<?php

namespace App\Services\SportsData;

use App\Enums\FixtureStatus;
use App\Models\Competition;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Read-side queries for the public football section. Reads only persisted data
 * (populated by the sync jobs), so page loads never call the provider directly.
 */
class FootballQuery
{
    /** Only show data from the provider currently configured (prevents demo/real mixing). */
    public function activeProviderKey(): ?string
    {
        return Cache::remember('football.active_provider', 60, function () {
            try {
                return app(ProviderResolver::class)->resolve()->key();
            } catch (\Throwable) {
                return null;
            }
        });
    }

    public function isDemo(): bool
    {
        return $this->activeProviderKey() === 'demo';
    }

    public function baseFixtures(): Builder
    {
        return Fixture::query()
            ->withCardRelations()
            ->where('provider', $this->activeProviderKey())
            ->whereHas('competition', fn ($q) => $q->where('is_active', true));
    }

    /**
     * @param  array{competition?: ?int, country?: ?int, team?: ?int}  $filters
     */
    public function fixtures(string $tab, CarbonInterface $from, CarbonInterface $to, array $filters = []): Collection
    {
        $query = $this->baseFixtures();

        match ($tab) {
            'live' => $query->live(),
            'upcoming' => $query->whereIn('status', [...FixtureStatus::UPCOMING, ...FixtureStatus::OFF])->whereBetween('kickoff_at', [$from, $to]),
            'results' => $query->finished()->whereBetween('kickoff_at', [$from, $to]),
            default => $query->whereBetween('kickoff_at', [$from, $to]),
        };

        $this->applyFilters($query, $filters);

        return $query->orderBy('kickoff_at')->orderBy('id')->limit(500)->get();
    }

    public function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['competition'] ?? null, fn ($q, $id) => $q->where('competition_id', $id))
            ->when($filters['country'] ?? null, fn ($q, $id) => $q->whereHas('competition', fn ($c) => $c->where('country_id', $id)))
            ->when($filters['team'] ?? null, fn ($q, $id) => $q->forTeam((int) $id));
    }

    /** Group fixtures by competition, ordered by the admin-defined competition position. */
    public function groupByCompetition(Collection $fixtures): Collection
    {
        $positions = Competition::query()->pluck('position', 'id');

        return $fixtures->groupBy('competition_id')
            ->sortBy(fn ($group, $id) => [$positions[$id] ?? 999, $group->first()->competition->name]);
    }

    public function liveCount(): int
    {
        return Cache::remember('football.live_count', 30, fn () => $this->baseFixtures()->live()->count());
    }

    public function featuredFixtures(int $limit = 6): Collection
    {
        $featured = $this->baseFixtures()->where('is_featured', true)
            ->where('kickoff_at', '>=', now()->subHours(3))
            ->orderBy('kickoff_at')->limit($limit)->get();

        if ($featured->count() >= $limit) {
            return $featured;
        }

        // Top up with the next fixtures from featured competitions.
        $more = $this->baseFixtures()
            ->whereNotIn('id', $featured->pluck('id'))
            ->whereHas('competition', fn ($q) => $q->where('is_featured', true))
            ->where(fn ($q) => $q->live()->orWhere(fn ($q) => $q->upcoming()))
            ->orderByRaw('CASE WHEN status IN ('.implode(',', array_fill(0, count(FixtureStatus::LIVE), '?')).') THEN 0 ELSE 1 END', FixtureStatus::LIVE)
            ->orderBy('kickoff_at')
            ->limit($limit - $featured->count())->get();

        return $featured->concat($more);
    }

    public function currentSeason(Competition $competition, ?int $year = null): ?Season
    {
        return $competition->seasons()
            ->when($year, fn ($q) => $q->where('year', $year), fn ($q) => $q->where('year', $competition->current_season_year))
            ->first() ?? ($year ? null : $competition->seasons()->first());
    }

    public function standings(Season $season): Collection
    {
        return Cache::remember("football.standings.{$season->id}", 300, fn () => $season->standings()
            ->with('team:id,name,short_name,slug,logo_url')
            ->get()
            ->groupBy('group_name'));
    }

    /** Last N finished results for a team, newest first. */
    public function recentForm(Team $team, int $limit = 5, ?CarbonInterface $before = null): Collection
    {
        return $this->baseFixtures()->finished()->forTeam($team->id)
            ->when($before, fn ($q) => $q->where('kickoff_at', '<', $before))
            ->orderByDesc('kickoff_at')->limit($limit)->get();
    }

    /** Previous meetings stored locally (used when the provider H2H is unavailable). */
    public function localHeadToHead(Fixture $fixture, int $limit = 10): Collection
    {
        $ids = [$fixture->home_team_id, $fixture->away_team_id];

        return $this->baseFixtures()->finished()
            ->whereIn('home_team_id', $ids)->whereIn('away_team_id', $ids)
            ->whereKeyNot($fixture->id)
            ->orderByDesc('kickoff_at')->limit($limit)->get();
    }

    public function teamFixtures(Team $team, bool $results, int $limit = 10): Collection
    {
        $query = $this->baseFixtures()->forTeam($team->id);

        return $results
            ? $query->finished()->orderByDesc('kickoff_at')->limit($limit)->get()
            : $query->where(fn ($q) => $q->upcoming()->orWhere(fn ($q) => $q->live()))->orderBy('kickoff_at')->limit($limit)->get();
    }
}
