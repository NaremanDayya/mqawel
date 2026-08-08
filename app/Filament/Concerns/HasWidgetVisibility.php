<?php

namespace App\Filament\Concerns;

use Illuminate\Support\Facades\Auth;

/**
 * Lets a dashboard widget be turned on/off per company via the
 * "تخصيص لوحة التحكم" settings stored on companies.dashboard_widgets,
 * keyed by the widget's class name.
 */
trait HasWidgetVisibility
{
    public static function canView(): bool
    {
        $company = Auth::user()?->company;

        return $company?->dashboard_widgets[static::class] ?? true;
    }
}
