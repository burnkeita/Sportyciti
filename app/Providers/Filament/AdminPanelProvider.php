<?php

namespace App\Providers\Filament;

use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->profile(isSimple: false)
            // Authenticator-app 2FA with recovery codes. Required for all staff when
            // STAFF_MFA_REQUIRED=true (defaults to on in production).
            ->multiFactorAuthentication(
                [AppAuthentication::make()->recoverable()],
                isRequired: fn () => (bool) config('auth.staff_mfa_required'),
            )
            ->brandName('SportyCiti Newsroom')
            ->brandLogo(asset('images/logo.webp'))
            ->darkModeBrandLogo(asset('images/logo-dark.webp'))
            ->brandLogoHeight('2.75rem')
            ->favicon(asset('images/icon-32.png'))
            ->colors([
                'primary' => Color::hex('#12B76A'),
                'warning' => Color::hex('#FF7A00'),
                'gray' => Color::Slate,
                'info' => Color::hex('#1F4A80'),
            ])
            ->font('Inter')
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->globalSearchKeyBindings(['ctrl+k', 'command+k'])
            ->navigationGroups([
                NavigationGroup::make('Newsroom'),
                NavigationGroup::make('Football'),
                NavigationGroup::make('Multimedia'),
                NavigationGroup::make('Engagement'),
                NavigationGroup::make('Advertising'),
                NavigationGroup::make('Site'),
                NavigationGroup::make('Administration'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
