<?php

namespace App\Filament\Pages\Widgets;

use App\Models\File;
use App\Models\Worker;
use App\Models\WorkerPayment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class StatsWorkerPayments extends BaseWidget
{
    //protected int | string | array $columnSpan= 'full';

    protected function getStats(): array
    {
        $number_of_workers_in_due= WorkerPayment::select('worker_id')->where('company_id', Auth::user()->company_id)->where('status', 'unpaid')->groupBy('worker_id')->get()->count();
        $number_of_procedures= WorkerPayment::where('company_id', Auth::user()->company_id)->where('status', 'unpaid')->count();
        $paid_due_amount= WorkerPayment::where('company_id', Auth::user()->company_id)->where('status', 'paid')->sum('amount');
        $unpaid_due_amount= WorkerPayment::where('company_id', Auth::user()->company_id)->where('status', 'unpaid')->sum('amount');

        $stats= [];

        $stats[]= Stat::make(__('backend.number_of_due_workers'), $number_of_workers_in_due)
            ->icon('heroicon-o-user-group');

        /*$stats[]= Stat::make(__('backend.number_of_procedures'), $number_of_procedures)
            ->icon('heroicon-o-document-currency-dollar');*/

        $stats[]= Stat::make(__('backend.paid_due_amount'), number_format($paid_due_amount, 2))
            ->icon('heroicon-o-currency-dollar');

        $stats[]= Stat::make(__('backend.unpaid_due_amount'), number_format($unpaid_due_amount, 2))
            ->icon('heroicon-o-currency-dollar');

        return $stats;
    }

    protected function getHeading(): ?string
    {
        return __('backend.analytics');
    }
}
