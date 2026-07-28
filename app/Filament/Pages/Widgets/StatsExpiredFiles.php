<?php

namespace App\Filament\Pages\Widgets;

use App\Models\File;
use App\Models\ProjectExpense;
use App\Models\Worker;
use App\Models\WorkerPayment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class StatsExpiredFiles extends BaseWidget
{
    //protected int | string | array $columnSpan= 'full';

    protected function getStats(): array
    {
        $number_of_files= File::where('company_id', Auth::user()->company_id)->count();
        $number_of_expired_files= File::where('company_id', Auth::user()->company_id)->where('expiry_date', '<', date('Y-m-d'))->count();
        $number_of_files_about_to_expire= File::where('company_id', Auth::user()->company_id)->where('expiry_date', '<=', Carbon::now()->addDays(Auth::user()->company->about_to_expire_days))->where('expiry_date', '>', Carbon::now())->count();

        $stats= [];

        $stats[]= Stat::make(__('backend.number_of_files'), $number_of_files)
            ->icon('heroicon-o-document-text');

        $stats[]= Stat::make(__('backend.number_of_expired_files'), $number_of_expired_files)
            ->icon('heroicon-o-document-minus');

        $stats[]= Stat::make(__('backend.number_of_files_about_to_expire'), $number_of_files_about_to_expire)
            ->icon('heroicon-o-document-arrow-down');

        return $stats;
    }

    protected function getHeading(): ?string
    {
        return __('backend.analytics');
    }
}
