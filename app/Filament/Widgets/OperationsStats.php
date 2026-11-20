<?php

namespace App\Filament\Widgets;

use App\Models\Advertisement;
use App\Models\SyncLog;
use App\Services\SportsData\ProviderResolver;
use App\Support\Permissions;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/** Advertising performance and sports-data integration health. */
class OperationsStats extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Operations';

    protected ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user && ($user->can(Permissions::MANAGE_ADS) || $user->can(Permissions::MANAGE_SPORTS_DATA));
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $stats = [];

        if ($user->can(Permissions::MANAGE_ADS)) {
            $ads = DB::table('advertisement_daily_stats')->where('date', '>=', now()->subDays(29)->toDateString())
                ->selectRaw('COALESCE(SUM(impressions),0) as i, COALESCE(SUM(clicks),0) as c')->first();
            $ctr = $ads->i > 0 ? round($ads->c / $ads->i * 100, 2) : 0;

            $stats[] = Stat::make('Active campaigns', Advertisement::query()->running()->count())->icon('heroicon-o-megaphone');
            $stats[] = Stat::make('Ad impressions (30d)', number_format((int) $ads->i))->description(number_format((int) $ads->c)." clicks · {$ctr}% CTR");
        }

        if ($user->can(Permissions::MANAGE_SPORTS_DATA)) {
            $resolver = app(ProviderResolver::class);
            try {
                $provider = $resolver->resolve();
                $name = $provider->isDemo() ? 'Demo data' : 'API-Football';
                $key = $provider->key();
            } catch (\Throwable) {
                $name = 'Not configured';
                $key = null;
            }

            $failures = SyncLog::where('status', 'failed')->where('created_at', '>=', now()->subDay())->count();
            $last = $key ? Cache::get("sportsdata.last_success.{$key}") : null;
            $circuitOpen = $key && Cache::has("sportsdata.circuit.{$key}.open");

            $stats[] = Stat::make('Sports API', $name)
                ->description($circuitOpen ? 'Paused after repeated failures' : ($last ? 'Last sync '.Carbon::parse($last)->diffForHumans() : 'No successful sync yet'))
                ->color($circuitOpen || ! $key ? 'danger' : ($provider->isDemo() ?? false ? 'warning' : 'success'))
                ->icon('heroicon-o-signal');
            $stats[] = Stat::make('Sync failures (24h)', $failures)->color($failures > 0 ? 'danger' : 'success');
        }

        return $stats;
    }
}
