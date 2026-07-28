<?php

namespace App\Filament\Widgets;

use App\Models\File;
use App\Models\Worker;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class StatsAlerts extends BaseWidget
{
    //protected int | string | array $columnSpan= 'full';

    protected static ?int $sort= 2;

    protected function getStats(): array
    {
        $expired_files= File::where('company_id', Auth::user()->company_id)->where('expiry_date', '<', date('Y-m-d'))->count();
        $files_about_to_expire= File::where('company_id', Auth::user()->company_id)->where('expiry_date', '<=', Carbon::now()->addDays(Auth::user()->company->about_to_expire_days))->where('expiry_date', '>', Carbon::now())->count();
        $incomplete_files= File::where('company_id', Auth::user()->company_id)->where('name', null)->orWhere('file', null)->orWhere('expiry_date', null)->count();
        $incomplete_workers= Worker::where('company_id', Auth::user()->company_id)->where(function($query){
            $query->where('picture', null);
            $query->orWhere('name', null);
            $query->orWhere('phone', null);
            $query->orWhere('ethnicity', null);
            $query->orWhere('living_address', null);
            $query->orWhere('job_title', null);
        })->count();

        $stats= [];

        if($expired_files > 0){
            $stats[]= Stat::make(__('backend.expired_files'), $expired_files)
            ->icon('heroicon-o-archive-box-x-mark')
            //->description(__('backend.requires_instant_review'))
            ->descriptionColor('danger')
            ->url(url: url('company/expired-files-report'), shouldOpenInNewTab:true);
        }

        if($files_about_to_expire > 0){
            $stats[]= Stat::make(__('backend.files_about_to_expire'), $files_about_to_expire)
            ->icon('heroicon-o-archive-box-arrow-down')
            //->description(__('backend.requires_observation'))
            ->descriptionColor('warning')
            ->url(url: url('company/expired-files-report'), shouldOpenInNewTab:true);
        }

        if($incomplete_files > 0){
            $stats[]= Stat::make(__('backend.incomplete_files'), $incomplete_files)
            ->icon('heroicon-o-archive-box')
            //->description(__('backend.requires_validation'))
            ->descriptionColor('info')
            ->url(url: url('company/expired-files-report'), shouldOpenInNewTab:true);
        }

        if($incomplete_workers > 0){
            $stats[]= Stat::make(__('backend.incomplete_workers'), $incomplete_workers)
            ->icon('heroicon-o-briefcase')
            //->description(__('backend.requires_validation'))
            ->descriptionColor('info')
            ->url(url: url('company/workers'), shouldOpenInNewTab:true);
        }

        return $stats;
    }

    protected function getHeading(): ?string
    {
        return __('backend.alerts');
    }

    protected function getDescription(): ?string
    {
        return __('backend.alerts_message');
    }
}
