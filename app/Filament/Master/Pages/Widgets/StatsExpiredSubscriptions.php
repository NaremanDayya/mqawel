<?php

namespace App\Filament\Master\Pages\Widgets;

use App\Models\Subscription;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsExpiredSubscriptions extends BaseWidget
{
    protected function getStats(): array
    {
        $number_of_subscriptions= Subscription::where('is_active', 1)->count();
        $number_of_expired_subscriptions= Subscription::where('is_active', 1)->where('ending_date', '<', date('Y-m-d'))->count();
        $number_of_subscriptions_about_to_expire= Subscription::where('ending_date', '<=', Carbon::now()->addDays(7))->where('ending_date', '>', Carbon::now())->count();

        $stats= [];

        $stats[]= Stat::make(__('backend.total_subscriptions'), $number_of_subscriptions)
            ->icon('heroicon-o-document-text');

        $stats[]= Stat::make(__('backend.number_of_expired_subscriptions'), $number_of_expired_subscriptions)
            ->icon('heroicon-o-document-minus');

        $stats[]= Stat::make(__('backend.number_of_subscriptions_about_to_expire'), $number_of_subscriptions_about_to_expire)
            ->icon('heroicon-o-document-arrow-down');

        return $stats;
    }

    protected function getHeading(): ?string
    {
        return __('backend.analytics');
    }
}
