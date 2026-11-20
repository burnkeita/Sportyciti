<?php

namespace App\Livewire\Football;

use App\Services\SportsData\FootballQuery;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** Compact live scores & upcoming fixtures widget for the homepage and sidebars. */
class LiveWidget extends Component
{
    #[Locked]
    public int $limit = 8;

    #[Locked]
    public string $variant = 'list'; // list | strip

    public function mount(int $limit = 8, string $variant = 'list'): void
    {
        $this->limit = max(1, min(20, $limit));
        $this->variant = in_array($variant, ['list', 'strip'], true) ? $variant : 'list';
    }

    public function render()
    {
        $football = app(FootballQuery::class);

        return view('livewire.football.live-widget', [
            'fixtures' => $football->featuredFixtures($this->limit),
            'liveCount' => $football->liveCount(),
            'isDemo' => $football->isDemo(),
        ]);
    }
}
