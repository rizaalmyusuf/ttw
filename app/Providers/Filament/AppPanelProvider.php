<?php
namespace App\Providers\Filament;

use App\Filament\Pages\Auth;
use Filament;
use Filament\Http\Middleware;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AppPanelProvider extends Filament\PanelProvider
{
    public function panel(Filament\Panel $panel): Filament\Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('')
            ->login(Auth\LoginApp::class)
            ->registration(Auth\RegistrationApp::class)
            ->profile(isSimple: false)
            ->favicon(asset('images/favicon.png'))
            ->colors([
                'primary' => Color::Indigo,
                'success' => Color::Emerald,
                'info' => Color::Cyan,
                'gray' => Color::Gray,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
            ])
            ->font('Montserrat')
            ->brandName('TTW')
            ->brandLogo(asset('images/logo.svg'))
            ->sidebarCollapsibleOnDesktop()
            ->spa()
            ->viteTheme('resources/css/filament/user/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                Middleware\AuthenticateSession::class,
                Middleware\DisableBladeIconComponents::class,
                Middleware\DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Middleware\Authenticate::class,
            ]);
    }
}
