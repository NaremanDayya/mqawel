<?php

namespace App\Filament\Pages\Widgets;

use App\Models\File;
use App\Models\Worker;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class StatsWorkers extends BaseWidget
{
    //protected int | string | array $columnSpan= 'full';

    protected function getStats(): array
    {
        $number_of_workers= Worker::where('company_id', Auth::user()->company_id)->count();
        $number_of_ethnicities= Worker::select('ethnicity')->where('company_id', Auth::user()->company_id)->where('ethnicity', '<>', null)->groupBy('ethnicity')->get()->count();
        $number_of_job_titles= Worker::select('job_title')->where('company_id', Auth::user()->company_id)->where('job_title', '<>', null)->groupBy('job_title')->get()->count();

        $stats= [];

        $stats[]= Stat::make(__('backend.number_of_workers'), $number_of_workers)
            ->icon('heroicon-o-user-group');

        $stats[]= Stat::make(__('backend.number_of_ethnicities'), $number_of_ethnicities)
            ->icon('heroicon-o-flag');

        $stats[]= Stat::make(__('backend.number_of_job_titles'), $number_of_job_titles)
            ->icon('heroicon-o-briefcase');

        return $stats;
    }

    protected function getHeading(): ?string
    {
        return __('backend.analytics');
    }
}
