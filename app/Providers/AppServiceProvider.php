<?php

namespace App\Providers;

use App\Models\Advertisement;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\Media;
use App\Models\User;
use App\Observers\ArticleObserver;
use App\Observers\MediaObserver;
use App\Services\Ads\AdService;
use App\Services\SportsData\Contracts\FootballDataProvider;
use App\Services\SportsData\FootballQuery;
use App\Services\SportsData\ProviderResolver;
use App\Support\Permissions;
use App\Support\Seo;
use App\View\Composers\LayoutComposer;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FootballDataProvider::class, fn ($app) => $app->make(ProviderResolver::class)->resolve());
        $this->app->scoped(Seo::class);
        $this->app->scoped(AdService::class);
        $this->app->singleton(FootballQuery::class);
    }

    public function boot(): void
    {
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());

        // Super Administrators pass every authorisation check.
        Gate::before(fn (User $user) => $user->hasRole(Permissions::SUPER_ADMIN) ? true : null);

        Article::observe(ArticleObserver::class);
        Media::observe(MediaObserver::class);
        Advertisement::saved(fn () => AdService::flushPlacementCache());
        Advertisement::deleted(fn () => AdService::flushPlacementCache());

        Paginator::defaultView('pagination.sportyciti');
        View::composer('layouts.app', LayoutComposer::class);

        Event::listen(Login::class, function (Login $event): void {
            if ($event->user instanceof User) {
                $event->user->forceFill(['last_login_at' => now(), 'last_login_ip' => request()->ip()])->saveQuietly();
                AuditLog::record('login', $event->user);
            }
        });

        RateLimiter::for('comments', fn (Request $request) => [
            Limit::perMinute(3)->by('c:'.($request->user()?->id ?: $request->ip())),
            Limit::perDay(50)->by('cd:'.($request->user()?->id ?: $request->ip())),
        ]);

        RateLimiter::for('newsletter', fn (Request $request) => Limit::perHour(5)->by('n:'.$request->ip()));

        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));
    }
}
