<?php

namespace App\Livewire\Football;

use App\Jobs\SyncFootballData;
use App\Models\Fixture;
use App\Services\SportsData\FootballQuery;
use App\Services\SportsData\FootballSyncService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Match centre: score header, timeline, statistics, line-ups, form and head-to-head.
 * Polls every 30 seconds while the match is live.
 */
class MatchCentre extends Component
{
    #[Locked]
    public int $fixtureId;

    #[Url(except: 'summary')]
    public string $section = 'summary';

    public function mount(Fixture $fixture): void
    {
        $this->fixtureId = $fixture->id;
        $this->requestDetailsIfStale($fixture);

        if (! in_array($this->section, ['summary', 'stats', 'lineups', 'h2h'], true)) {
            $this->section = 'summary';
        }
    }

    public function show(string $section): void
    {
        $this->section = in_array($section, ['summary', 'stats', 'lineups', 'h2h'], true) ? $section : 'summary';
    }

    #[Computed]
    public function fixture(): Fixture
    {
        return Fixture::query()
            ->with(['homeTeam', 'awayTeam', 'competition', 'events', 'lineups', 'statistics'])
            ->findOrFail($this->fixtureId);
    }

    /** Stats as [type => [home, away]] preserving provider order. */
    #[Computed]
    public function stats(): Collection
    {
        $fixture = $this->fixture;

        return $fixture->statistics->groupBy('type')->map(fn ($rows) => [
            'home' => $rows->firstWhere('team_id', $fixture->home_team_id)?->value,
            'away' => $rows->firstWhere('team_id', $fixture->away_team_id)?->value,
        ]);
    }

    #[Computed]
    public function headToHead(): array
    {
        $fixture = $this->fixture;
        $football = app(FootballQuery::class);

        try {
            $rows = app(FootballSyncService::class)->headToHead($fixture);
        } catch (\Throwable) {
            $rows = [];
        }

        if ($rows === []) {
            return ['source' => 'local', 'meetings' => $football->localHeadToHead($fixture)];
        }

        return ['source' => 'provider', 'meetings' => collect($rows)->filter(fn ($r) => in_array($r['status'], ['FT', 'AET', 'PEN'], true))->take(10)];
    }

    #[Computed]
    public function form(): array
    {
        $fixture = $this->fixture;
        $football = app(FootballQuery::class);

        return [
            'home' => $football->recentForm($fixture->homeTeam, 5, $fixture->kickoff_at),
            'away' => $football->recentForm($fixture->awayTeam, 5, $fixture->kickoff_at),
        ];
    }

    public function refresh(): void
    {
        unset($this->fixture, $this->stats);
        $this->requestDetailsIfStale($this->fixture);
    }

    public function render()
    {
        return view('livewire.football.match-centre', [
            'isDemo' => $this->fixture->isDemo(),
        ]);
    }

    /** Queue a details sync (events/line-ups/stats) when they are missing or stale; throttled per fixture. */
    private function requestDetailsIfStale(Fixture $fixture): void
    {
        $kickoffSoon = $fixture->kickoff_at->between(now()->subHours(3), now()->addHour());
        $stale = ! $fixture->details_synced_at
            || ($fixture->isLive() && $fixture->details_synced_at->lt(now()->subMinutes(2)));

        if (($fixture->hasStarted() || $kickoffSoon) && $stale && Cache::add("football.details_requested.{$fixture->id}", true, 120)) {
            SyncFootballData::dispatch('fixture', fixtureId: $fixture->id);
        }
    }
}
