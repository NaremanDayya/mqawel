<?php

namespace App\Providers\Filament;

use App\Http\Middleware\SetLang;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use App\Filament\Pages\Auth\Register;
use App\Http\Middleware\AdminAccessPanelMiddleware;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\View\View;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->favicon(asset('images/logo-mark.png'))
            ->brandLogo(fn () => view('filament.hooks.brand'))
            ->brandLogoHeight('auto')
            ->id('company')
            ->path('company')
            ->login()
            ->registration(action:Register::class)
            ->passwordReset()
            ->colors([
                'primary' => Color::hex('#6E56CF'),
                'danger' => Color::hex('#E5484D'),
                'success' => Color::hex('#12A594'),
                'warning' => Color::hex('#F5A524'),
                'info' => Color::hex('#3E63DD'),
            ])
            ->font('IBM Plex Sans Arabic', url: 'https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap')
            ->globalSearchKeyBindings(['ctrl+k', 'command+k'])
            ->globalSearchFieldSuffix(fn (): string => 'Ctrl K')
            ->sidebarCollapsibleOnDesktop(true)
            ->sidebarWidth('268px')
            ->collapsedSidebarWidth('76px')
            ->darkMode(false)
            ->navigationGroups([
                NavigationGroup::make(__('backend.company'))
                    ->icon('heroicon-o-briefcase')
                    ->collapsible(),
                NavigationGroup::make(__('backend.projects'))
                    ->icon('heroicon-o-folder')
                    ->collapsible(),
                NavigationGroup::make(__('backend.entities'))
                    ->icon('heroicon-o-building-office-2')
                    ->collapsible(),
                NavigationGroup::make(__('backend.storages'))
                    ->icon('heroicon-o-archive-box')
                    ->collapsible(),
                NavigationGroup::make(__('backend.contacts'))
                    ->icon('heroicon-o-inbox')
                    ->collapsible(),
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->renderHook(PanelsRenderHook::USER_MENU_PROFILE_AFTER, fn(): View => view('filament.hooks.profile'),)
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            //->databaseNotifications()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                /*Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,*/
            ])
            ->navigationItems([
                NavigationItem::make(__('backend.settings'))
                ->label(fn() => __('backend.settings'))
                ->url(fn (): string => '/company/companies/'.Auth::user()->company_id.'/edit')
                ->icon('heroicon-o-cog')
                ->group(fn() => __('backend.company'))
                ->sort(4),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetLang::class,
                AdminAccessPanelMiddleware::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
