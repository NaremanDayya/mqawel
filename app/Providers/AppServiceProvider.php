<?php

namespace App\Providers;

use App\Http\Responses\Auth\PanelScopedLoginResponse;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Tables\Actions\CreateAction as TableCreateAction;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LoginResponse::class, PanelScopedLoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Native HTML date inputs render inconsistently on an RTL page (segment
        // order follows OS/browser locale, not the app's direction), which is
        // what made dates look reversed. Filament's own JS picker renders the
        // same way everywhere regardless of OS locale.
        //
        // DatePicker extends DateTimePicker, so a DatePicker instance matches
        // both configureUsing() calls below; whichever runs last wins. This
        // one must stay registered first so DatePicker's own d/m/Y format
        // overrides it for DatePicker fields, while genuine DateTimePicker
        // fields (not instanceof DatePicker) are unaffected by the order.
        DateTimePicker::configureUsing(fn (DateTimePicker $component) => $component->native(false)->displayFormat('d/m/Y H:i'));
        DatePicker::configureUsing(fn (DatePicker $component) => $component->native(false)->displayFormat('d/m/Y'));

        // Every "Create" modal was showing two near-identical submit buttons
        // (the normal one plus Filament's built-in "create & create another"),
        // which read as a duplicated button rather than two distinct actions.
        // Table actions and page/header actions are separate class hierarchies
        // in Filament, so both need configuring.
        CreateAction::configureUsing(fn (CreateAction $action) => $action->createAnother(false));
        TableCreateAction::configureUsing(fn (TableCreateAction $action) => $action->createAnother(false));
    }
}
