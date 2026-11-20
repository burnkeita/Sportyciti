<?php

namespace App\Livewire\Football;

use App\Models\Competition;
use App\Models\Team;
use App\Services\SportsData\FootballQuery;
use App\Support\VisitorTime;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Fixtures or results over a day / week / month window, filterable by league and team.
 */
class FixtureBrowser extends Component
{
    #[Locked]
    public string $mode = 'fixtures'; // fixtures | results

    #[Locked]
    public ?int $lockedCompetition = null;

    #[Url(except: 'week')]
    public string $range = 'week';

    #[Url(except: 0)]
    public int $offset = 0;

    #[Url(except: null)]
    public ?int $competition = null;

    #[Url(except: '')]
    public string $team = '';

    public function mount(string $mode = 'fixtures', ?int $competitionId = null): void
    {
        $this->mode = in_array($mode, ['fixtures', 'results', 'all'], true) ? $mode : 'fixtures';
        $this->lockedCompetition = $competitionId;
        $this->sanitise();
    }

    public function updated(): void
    {
        $this->sanitise();
    }

    public function updatedRange(): void
    {
        $this->offset = 0;
    }

    public function move(int $step): void
    {
        $this->offset = max(-24, min(24, $this->offset + ($step > 0 ? 1 : -1)));
    }

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} local start/end of the window */
    public function window(): array
    {
        $today = VisitorTime::today();
        $direction = $this->mode === 'results' ? -1 : 1;

        return match ($this->range) {
            'day' => [$start = $today->addDays($this->offset), $start->endOfDay()],
            'month' => [$start = $today->addMonths($this->offset)->startOfMonth(), $start->endOfMonth()],
            default => $direction === 1
                ? [$start = $today->addWeeks($this->offset), $start->addDays(6)->endOfDay()]
                : [$start = $today->addWeeks($this->offset)->subDays(6), $today->addWeeks($this->offset)->endOfDay()],
        };
    }

    #[Computed]
    public function days(): Collection
    {
        [$start, $end] = $this->window();
        $football = app(FootballQuery::class);
        $tab = match ($this->mode) {
            'results' => 'results',
            'fixtures' => 'upcoming',
            default => 'all',
        };

        $teamId = $this->team !== '' ? Team::where('slug', $this->team)->value('id') : null;

        $fixtures = $football->fixtures($tab, $start->utc(), $end->utc(), [
            'competition' => $this->lockedCompetition ?? $this->competition,
            'team' => $teamId,
        ]);

        if ($this->mode === 'fixtures') {
            $fixtures = $fixtures->merge($football->fixtures('live', $start->utc(), $end->utc(), ['competition' => $this->lockedCompetition ?? $this->competition, 'team' => $teamId]))->unique('id')->sortBy('kickoff_at');
        }

        $tz = VisitorTime::timezone();
        $grouped = $fixtures->groupBy(fn ($f) => $f->kickoff_at->copy()->setTimezone($tz)->toDateString());

        return $this->mode === 'results' ? $grouped->sortKeysDesc() : $grouped->sortKeys();
    }

    public function render()
    {
        $football = app(FootballQuery::class);
        [$start, $end] = $this->window();
        $competitionId = $this->lockedCompetition ?? $this->competition;

        return view('livewire.football.fixture-browser', [
            'competitions' => $this->lockedCompetition ? collect() : Competition::query()->active()->where('provider', $football->activeProviderKey())->get(['id', 'name']),
            'teams' => Team::query()
                ->where('provider', $football->activeProviderKey())
                ->when($competitionId, fn ($q) => $q->whereHas('competitions', fn ($c) => $c->where('competitions.id', $competitionId)))
                ->orderBy('name')->limit(400)->get(['id', 'name', 'slug']),
            'start' => $start,
            'end' => $end,
            'isDemo' => $football->isDemo(),
        ]);
    }

    private function sanitise(): void
    {
        if (! in_array($this->range, ['day', 'week', 'month'], true)) {
            $this->range = 'week';
        }
        $this->offset = max(-24, min(24, $this->offset));
        $this->team = preg_match('/^[a-z0-9-]{0,191}$/', $this->team) ? $this->team : '';
    }
}
