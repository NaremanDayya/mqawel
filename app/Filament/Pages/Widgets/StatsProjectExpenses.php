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

class StatsProjectExpenses extends BaseWidget
{
    //protected int | string | array $columnSpan= 'full';

    protected function getStats(): array
    {
        $number_of_projects_with_expenses= ProjectExpense::select('project_id')->where('company_id', Auth::user()->company_id)->groupBy('project_id')->get()->count();
        $number_of_procedures= ProjectExpense::where('company_id', Auth::user()->company_id)->count();
        $total_expenses= ProjectExpense::where('company_id', Auth::user()->company_id)->sum('amount');

        $stats= [];

        $stats[]= Stat::make(__('backend.number_of_projects_with_expenses'), $number_of_projects_with_expenses)
            ->icon('heroicon-o-map-pin');

        $stats[]= Stat::make(__('backend.number_of_procedures'), $number_of_procedures)
            ->icon('heroicon-o-document-currency-dollar');

        $stats[]= Stat::make(__('backend.total_expenses'), number_format($total_expenses, 2))
            ->icon('heroicon-o-currency-dollar');

        return $stats;
    }

    protected function getHeading(): ?string
    {
        return __('backend.analytics');
    }
}
