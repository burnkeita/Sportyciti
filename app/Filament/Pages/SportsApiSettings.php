<?php

namespace App\Filament\Pages;

use App\Jobs\SyncFootballData;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\SyncLog;
use App\Services\SportsData\ProviderResolver;
use App\Services\SportsData\Providers\ApiFootballProvider;
use App\Services\SportsData\Providers\DemoProvider;
use App\Support\Permissions;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use UnitEnum;

/**
 * Sports data provider configuration. The API key is stored encrypted and is
 * never sent back to the browser.
 *
 * @property-read Schema $form
 */
class SportsApiSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-server-stack';

    protected static string|UnitEnum|null $navigationGroup = 'Football';

    protected static ?int $navigationSort = 6;

    protected static ?string $title = 'Sports API settings';

    protected string $view = 'filament.pages.settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->can(Permissions::MANAGE_SPORTS_DATA) ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'provider' => Setting::get('sportsdata.provider') ?: config('sportsdata.provider'),
            'api_key' => null,
            'requests_per_minute' => Setting::get('sportsdata.requests_per_minute') ?: config('sportsdata.api_football.requests_per_minute'),
            'demo_fallback' => app(ProviderResolver::class)->demoFallbackEnabled(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $hasKey = filled(app(ProviderResolver::class)->apiFootballKey());

        return $schema->statePath('data')->components([
            Section::make('Provider')->columns(2)->schema([
                Select::make('provider')->options([
                    ApiFootballProvider::KEY => 'API-Football (api-sports.io) — licensed live data',
                    DemoProvider::KEY => 'Demonstration data (fictional, development only)',
                ])->required(),
                TextInput::make('requests_per_minute')->label('Plan requests per minute')->numeric()->minValue(1)->maxValue(1000)
                    ->helperText('Free 10 · Pro 300 · Ultra 450 · Mega 900'),
                TextInput::make('api_key')->label('API-Football key')->password()->revealable()
                    ->placeholder($hasKey ? '•••••••• (stored — leave blank to keep)' : 'Not configured')
                    ->helperText('Stored encrypted. An API_FOOTBALL_KEY in .env is used if no key is saved here.')
                    ->columnSpanFull(),
                Toggle::make('demo_fallback')->label('Use demonstration data when no API key is configured')
                    ->helperText('Keep this OFF in production so fictional data can never appear on the live site.')
                    ->columnSpanFull(),
            ]),
        ]);
    }

    /** @return array<string, string> */
    public function getStatus(): array
    {
        $resolver = app(ProviderResolver::class);

        try {
            $provider = $resolver->resolve();
            $active = $provider->isDemo() ? 'Demo data (fictional)' : 'API-Football';
            $key = $provider->key();
        } catch (\Throwable $e) {
            $active = 'Not configured';
            $key = null;
        }

        $last = $key ? Cache::get("sportsdata.last_success.{$key}") : null;
        $remaining = Cache::get('apifootball:daily_remaining');
        $limit = Cache::get('apifootball:daily_limit');

        return [
            'Active provider' => $active,
            'Last successful sync' => $last ? Carbon::parse($last)->diffForHumans() : 'never',
            'Failures (24h)' => (string) SyncLog::where('status', 'failed')->where('created_at', '>=', now()->subDay())->count(),
            'Daily quota remaining' => $remaining !== null ? "{$remaining}".($limit ? " / {$limit}" : '') : 'unknown',
            'Circuit breaker' => $key && Cache::has("sportsdata.circuit.{$key}.open") ? 'OPEN — paused' : 'closed (normal)',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncNow')->label('Sync fixtures & live scores now')->color('gray')->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->modalDescription('Queues date-window, live and standings syncs. Each uses provider quota.')
                ->action(function () {
                    foreach (['dates', 'live', 'standings'] as $resource) {
                        SyncFootballData::dispatch($resource);
                    }
                    AuditLog::record('sports_sync_requested', null, ['resource' => 'dates,live,standings']);
                    Notification::make()->title('Sync queued')->body('Check Sync logs for results.')->success()->send();
                }),
            Action::make('fullSeason')->label('Full season sync')->color('gray')->icon('heroicon-o-calendar')
                ->requiresConfirmation()
                ->modalDescription('Downloads complete season fixtures for every enabled competition (one request per competition).')
                ->action(function () {
                    SyncFootballData::dispatch('season');
                    Notification::make()->title('Season sync queued')->success()->send();
                }),
            Action::make('resetCircuit')->label('Reset circuit breaker')->color('gray')->icon('heroicon-o-bolt')
                ->action(function () {
                    foreach ([ApiFootballProvider::KEY, DemoProvider::KEY] as $key) {
                        Cache::forget("sportsdata.circuit.{$key}.open");
                        Cache::forget("sportsdata.circuit.{$key}.failures");
                    }
                    Notification::make()->title('Circuit breaker reset')->success()->send();
                }),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Setting::put('sportsdata.provider', $state['provider']);
        Setting::put('sportsdata.requests_per_minute', (int) $state['requests_per_minute']);
        Setting::put('sportsdata.demo_fallback', (bool) $state['demo_fallback']);

        if (filled($state['api_key'] ?? null)) {
            Setting::put('sportsdata.api_football_key', Crypt::encryptString(trim($state['api_key'])));
        }

        Cache::forget('football.active_provider');
        AuditLog::record('sports_settings_updated', null, ['provider' => $state['provider'], 'key_changed' => filled($state['api_key'] ?? null)]);

        $this->form->fill([...$state, 'api_key' => null]);
        Notification::make()->title('Sports API settings saved')->success()->send();
    }
}
