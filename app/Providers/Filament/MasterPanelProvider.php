<?php

namespace App\Providers\Filament;

use App\Http\Middleware\SetLang;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
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
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\View\View;

class MasterPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->favicon(asset('images/logo-mark.png'))
            ->brandLogo(fn () => view('filament.hooks.brand'))
            ->brandLogoHeight('auto')
            ->id('master')
            ->path('master')
            ->login()
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
                NavigationGroup::make(__('backend.companies'))
                    ->icon('heroicon-o-briefcase')
                    ->collapsible(),
                NavigationGroup::make(__('backend.subscriptions'))
                    ->icon('heroicon-o-credit-card')
                    ->collapsible(),
                NavigationGroup::make(__('backend.contacts'))
                    ->icon('heroicon-o-inbox')
                    ->collapsible(),
                NavigationGroup::make(__('backend.system_admin'))
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->collapsible(),
                NavigationGroup::make(__('backend.reports'))
                    ->icon('heroicon-o-chart-bar')
                    ->collapsible(),
            ])
            ->viteTheme('resources/css/filament/admin/theme_master.css')
            ->renderHook(PanelsRenderHook::USER_MENU_PROFILE_AFTER, fn(): View => view('filament.hooks.edit-master'),)
            ->renderHook(PanelsRenderHook::USER_MENU_PROFILE_AFTER, fn(): View => view('filament.hooks.lang-switcher'),)
            ->renderHook(PanelsRenderHook::BODY_END, fn(): View => view('filament.hooks.lang-switch-guard'),)
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            //->databaseNotifications()
            ->discoverResources(in: app_path('Filament/Master/Resources'), for: 'App\\Filament\\Master\\Resources')
            ->discoverPages(in: app_path('Filament/Master/Pages'), for: 'App\\Filament\\Master\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Master/Widgets'), for: 'App\\Filament\\Master\\Widgets')
            ->widgets([
                /*Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,*/
            ])
            ->navigationItems([
                NavigationItem::make(__('backend.settings'))
                ->url(fn (): string => '/master/settings/1/edit')
                ->icon('heroicon-o-cog')
                ->group(__('backend.system_admin'))
                ->sort(8),
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('master');
    }
}
