<?php

namespace App\Filament\Widgets;

use App\Support\Permissions;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PopularCategoriesChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Popular categories — views, last 30 days';

    protected ?string $maxHeight = '280px';

    public static function canView(): bool
    {
        return auth()->user()?->can(Permissions::VIEW_ANALYTICS) ?? false;
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $rows = DB::table('article_views')
            ->join('articles', 'articles.id', '=', 'article_views.article_id')
            ->join('categories', 'categories.id', '=', DB::raw('COALESCE(articles.subcategory_id, articles.category_id)'))
            ->where('article_views.date', '>=', now()->subDays(29)->toDateString())
            ->groupBy('categories.id', 'categories.name')
            ->selectRaw('categories.name as name, SUM(article_views.views) as total')
            ->orderByDesc('total')->limit(10)->get();

        return [
            'datasets' => [[
                'label' => 'Views',
                'data' => $rows->pluck('total')->map(fn ($v) => (int) $v)->all(),
                'backgroundColor' => '#12B76A',
                'borderRadius' => 6,
            ]],
            'labels' => $rows->pluck('name')->all(),
        ];
    }

    protected function getOptions(): array
    {
        return ['indexAxis' => 'y', 'plugins' => ['legend' => ['display' => false]]];
    }
}
