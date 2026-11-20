<?php

namespace App\Filament\Widgets;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use App\Support\Permissions;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class NewsroomStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '120s';

    protected function getStats(): array
    {
        $user = auth()->user();
        $own = ! $user->can(Permissions::EDIT_ANY_ARTICLE);
        $articles = fn () => Article::query()->when($own, fn ($q) => $q->where('author_id', $user->id));

        $views = collect(DB::table('article_views')->where('date', '>=', now()->subDays(13)->toDateString())
            ->selectRaw('date, SUM(views) as total')->groupBy('date')->orderBy('date')->pluck('total', 'date'));
        $last7 = (int) $views->filter(fn ($v, $d) => $d >= now()->subDays(6)->toDateString())->sum();
        $prev7 = (int) $views->sum() - $last7;
        $trend = $prev7 > 0 ? round(($last7 - $prev7) / $prev7 * 100) : null;

        $stats = [
            Stat::make('Published articles', number_format($articles()->where('status', ArticleStatus::Published)->count()))
                ->description($articles()->where('status', ArticleStatus::Published)->where('published_at', '>=', now()->subDays(7))->count().' this week')
                ->icon('heroicon-o-newspaper'),
            Stat::make('Drafts', number_format($articles()->where('status', ArticleStatus::Draft)->count()))->icon('heroicon-o-pencil-square'),
            Stat::make('Pending approval', number_format($articles()->where('status', ArticleStatus::InReview)->count()))
                ->color('warning')->icon('heroicon-o-clock'),
            Stat::make('Scheduled', number_format($articles()->where('status', ArticleStatus::Scheduled)->count()))->icon('heroicon-o-calendar'),
        ];

        if ($user->can(Permissions::VIEW_ANALYTICS)) {
            $stats[] = Stat::make('Article views (7 days)', number_format($last7))
                ->description($trend === null ? 'No previous data' : ($trend >= 0 ? "+{$trend}%" : "{$trend}%").' vs previous 7 days')
                ->descriptionIcon($trend !== null && $trend < 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrow-trending-up')
                ->color($trend !== null && $trend < 0 ? 'danger' : 'success')
                ->chart($views->values()->map(fn ($v) => (int) $v)->all());
            $stats[] = Stat::make('Authors', number_format(User::permission(Permissions::CREATE_ARTICLES)->count()))->icon('heroicon-o-user-group');
            $stats[] = Stat::make('Newsletter subscribers', number_format(NewsletterSubscriber::where('status', 'confirmed')->count()))
                ->description(NewsletterSubscriber::where('status', 'confirmed')->where('confirmed_at', '>=', now()->subDays(7))->count().' new this week')
                ->icon('heroicon-o-envelope');
        }

        return $stats;
    }
}
