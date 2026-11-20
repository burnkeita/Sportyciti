<?php

namespace App\View\Composers;

use App\Models\Category;
use App\Models\Competition;
use App\Models\Setting;
use App\Services\Content\ContentQuery;
use App\Services\SportsData\FootballQuery;
use App\Support\Seo;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class LayoutComposer
{
    public function __construct(
        private readonly ContentQuery $content,
        private readonly FootballQuery $football,
        private readonly Seo $seo,
    ) {}

    public function compose(View $view): void
    {
        $view->with([
            'seo' => $this->seo,
            'navCategories' => Category::navigation(),
            'navCompetitions' => Cache::remember('nav.competitions', 600, fn () => Competition::query()->active()->where('is_featured', true)->get(['id', 'name', 'short_name', 'slug', 'provider'])),
            'breaking' => $this->content->breaking(),
            'liveCount' => rescue(fn () => $this->football->liveCount(), 0, false),
            'socialLinks' => array_filter((array) Setting::get('social.links', [])),
            'siteName' => Setting::get('site.name', config('app.name')),
            'adsenseClient' => Setting::get('ads.adsense_client'),
        ]);
    }
}
