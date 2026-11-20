<?php

namespace App\Livewire\Football;

use App\Models\Competition;
use App\Models\Country;
use App\Services\SportsData\FootballQuery;
use App\Support\VisitorTime;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Live / upcoming / completed match tabs with date, country and competition filters.
 * Polls every 30 seconds while showing live or same-day matches.
 */
class ScoresBoard extends Component
{
    #[Url(except: 'all')]
    public string $tab = 'all';

    #[Url(except: '')]
    public string $date = '';

    #[Url(except: null)]
    public ?int $competition = null;

    #[Url(except: null)]
    public ?int $country = null;

    public function mount(): void
    {
        $this->sanitise();
    }

    public function updated(): void
    {
        $this->sanitise();
        unset($this->groups);
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->sanitise();
    }

    public function shiftDate(int $days): void
    {
        $this->date = $this->day()->addDays(max(-7, min(7, $days)))->toDateString();
        $this->sanitise();
    }

    public function today(): void
    {
        $this->date = '';
    }

    #[Computed]
    public function groups(): Collection
    {
        $football = app(FootballQuery::class);
        [$from, $to] = VisitorTime::dayBounds($this->day()->toDateString());

        $fixtures = $football->fixtures($this->tab, $from, $to, [
            'competition' => $this->competition,
            'country' => $this->country,
        ]);

        return $football->groupByCompetition($fixtures);
    }

    public function day(): CarbonImmutable
    {
        return $this->date !== '' ? CarbonImmutable::parse($this->date, VisitorTime::timezone()) : VisitorTime::today();
    }

    public function shouldPoll(): bool
    {
        return $this->tab === 'live' || $this->day()->isSameDay(VisitorTime::today());
    }

    public function render()
    {
        $football = app(FootballQuery::class);

        return view('livewire.football.scores-board', [
            'competitions' => Competition::query()->active()->where('provider', $football->activeProviderKey())->get(['id', 'name', 'country_id']),
            'countries' => Country::query()->whereHas('competitions', fn ($q) => $q->where('is_active', true)->where('provider', $football->activeProviderKey()))->orderBy('name')->get(['id', 'name']),
            'liveCount' => $football->liveCount(),
            'isDemo' => $football->isDemo(),
            'days' => collect(range(-3, 3))->map(fn ($d) => VisitorTime::today()->addDays($d)),
        ]);
    }

    private function sanitise(): void
    {
        if (! in_array($this->tab, ['all', 'live', 'upcoming', 'results'], true)) {
            $this->tab = 'all';
        }

        if ($this->date !== '') {
            try {
                $parsed = CarbonImmutable::createFromFormat('Y-m-d', $this->date, VisitorTime::timezone());
                $offset = (int) VisitorTime::today()->diffInDays($parsed->startOfDay(), false);
                $this->date = abs($offset) > 30 || $offset === 0 ? '' : $parsed->toDateString();
            } catch (\Throwable) {
                $this->date = '';
            }
        }
    }
}
