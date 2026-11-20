<?php

namespace App\Jobs;

use App\Enums\FixtureStatus;
use App\Models\Competition;
use App\Models\Fixture;
use App\Services\SportsData\FootballSyncService;
use App\Services\SportsData\SyncRunner;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

/**
 * Queue job for every football sync operation.
 *
 * Resources: live | dates | date | season | standings | fixture
 */
class SyncFootballData implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 300;

    public int $uniqueFor = 300;

    public function __construct(
        public string $resource,
        public ?int $competitionId = null,
        public ?string $date = null,
        public ?int $fixtureId = null,
    ) {}

    public function uniqueId(): string
    {
        return implode(':', [$this->resource, $this->competitionId, $this->date, $this->fixtureId]);
    }

    public function handle(SyncRunner $runner): void
    {
        match ($this->resource) {
            'live' => $this->live($runner),
            'dates' => $this->dates($runner),
            'date' => $runner->run('fixtures:date', fn (FootballSyncService $s) => $s->syncDate(Carbon::parse($this->date ?? 'today', 'UTC')), ['date' => $this->date]),
            'season' => $this->perCompetition($runner, 'fixtures:season', fn (FootballSyncService $s, Competition $c) => $s->syncSeason($c)),
            'standings' => $this->perCompetition($runner, 'standings', fn (FootballSyncService $s, Competition $c) => $s->syncStandings($c)),
            'fixture' => $this->fixture($runner),
            default => throw new \InvalidArgumentException("Unknown football sync resource [{$this->resource}]"),
        };
    }

    /** Only spend a request on live data when a match is in play or about to start. */
    private function live(SyncRunner $runner): void
    {
        $provider = $runner->service()->provider()->key();

        $active = Fixture::query()->where('provider', $provider)
            ->where(fn ($q) => $q->whereIn('status', FixtureStatus::LIVE)
                ->orWhere(fn ($q) => $q->whereIn('status', FixtureStatus::UPCOMING)->whereBetween('kickoff_at', [now()->subHours(3), now()->addMinutes(15)])))
            ->exists();

        if ($active || $runner->service()->provider()->isDemo()) {
            $runner->run('fixtures:live', fn (FootballSyncService $s) => $s->syncLive());
        }
    }

    private function dates(SyncRunner $runner): void
    {
        $behind = (int) config('sportsdata.sync_days_behind');
        $ahead = (int) config('sportsdata.sync_days_ahead');

        for ($d = -$behind; $d <= $ahead; $d++) {
            $date = now('UTC')->addDays($d)->startOfDay();
            $runner->run('fixtures:date', fn (FootballSyncService $s) => $s->syncDate($date), ['date' => $date->toDateString()]);
        }
    }

    private function perCompetition(SyncRunner $runner, string $resource, callable $operation): void
    {
        $competitions = $runner->service()->competitions();

        if ($this->competitionId) {
            $competitions = $competitions->where('id', $this->competitionId);
        }

        foreach ($competitions as $competition) {
            $runner->run($resource, fn (FootballSyncService $s) => $operation($s, $competition), ['competition' => $competition->slug]);
        }
    }

    private function fixture(SyncRunner $runner): void
    {
        $fixture = Fixture::find($this->fixtureId);

        if ($fixture) {
            $runner->run('fixtures:details', fn (FootballSyncService $s) => (int) $s->syncFixtureDetails($fixture), ['fixture' => $fixture->id]);
        }
    }
}
