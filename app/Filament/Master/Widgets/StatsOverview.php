<?php

namespace App\Filament\Master\Widgets;

use App\Models\Company;
use App\Models\Project;
use App\Models\Subscription;
use App\Models\Worker;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval= '15s';

    protected static bool $isLazy= true;

    protected static ?int $sort= 2;

    protected function getStats(): array
    {
        return [
            Stat::make(__('backend.total_projects'), Project::count())
            ->description(__('backend.growth_indicator'))
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success')
            ->chart($this->getProjectsByMonth('')['projectsPerMonth'])
            ->url(url: url('master/projects'), shouldOpenInNewTab:true),
            
            Stat::make(__('backend.pending_projects'), Project::where(['status' => 'pending'])->count())
            ->description(__('backend.growth_indicator'))
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('warning')
            ->chart($this->getProjectsByMonth('pending')['projectsPerMonth'])
            ->url(url: url('master/projects?tableFilters[status][value]=pending'), shouldOpenInNewTab:true),

            Stat::make(__('backend.processing_projects'), Project::where(['status' => 'processing'])->count())
            ->description(__('backend.growth_indicator'))
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('primary')
            ->chart($this->getProjectsByMonth('processing')['projectsPerMonth'])
            ->url(url: url('master/projects?tableFilters[status][value]=processing'), shouldOpenInNewTab:true),

            Stat::make(__('backend.completed_projects'), Project::where(['status' => 'completed'])->count())
            ->description(__('backend.growth_indicator'))
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success')
            ->chart($this->getProjectsByMonth('completed')['projectsPerMonth'])
            ->url(url: url('master/projects?tableFilters[status][value]=completed'), shouldOpenInNewTab:true),

            Stat::make(__('backend.total_companies'), Company::where(['is_active' => true])->count())
            ->description(__('backend.growth_indicator'))
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('primary')
            ->chart($this->getCompaniesByMonth('')['companies_by_month'])
            ->url(url: url('master/companies'), shouldOpenInNewTab:true),

            Stat::make(__('backend.total_workers'), Worker::count())
            ->description(__('backend.growth_indicator'))
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('primary')
            ->chart($this->getWorkersByMonth('')['workers_by_month']),

            Stat::make(__('backend.total_subscriptions'), Subscription::where(['is_active' => true])->count())
            ->description(__('backend.growth_indicator'))
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('primary')
            ->chart($this->getSubscriptionsByMonth('')['subscriptions_by_month'])
            ->url(url: url('master/subscriptions'), shouldOpenInNewTab:true),

            Stat::make(__('backend.expired_subscriptions'), Subscription::where(['is_active' => true])->where('ending_date', '<', date('Y-m-d'))->count())
            ->description(__('backend.subscription'))
            ->descriptionIcon('heroicon-o-clock')
            ->color('primary')
            ->url(url: url('master/expired-subscriptions-report'), shouldOpenInNewTab:true),

            Stat::make(__('backend.total_revenue'), Subscription::where(['is_active' => true])->sum('price'))
            ->description(__('backend.total_subscriptions'))
            ->descriptionIcon('heroicon-o-banknotes')
            ->color('success')
        ];
    }

    private function getProjectsByMonth($status) : array{
        $now= Carbon::now();

        $projectsPerMonth= [];

        for($i= 1; $i < 13; $i++){
            $count= Project::when(!empty($status), function($query) use($status){
                $query->where('status', $status);
            })
            ->whereMonth('created_at', Carbon::parse($now->month($i)->format('Y-m')))->count();

            $projectsPerMonth[]= $count;
        }

        return [
            'projectsPerMonth' => $projectsPerMonth,
        ];
    }

    private function getCompaniesByMonth($status) : array{
        $now= Carbon::now();

        $companies_by_month= [];

        for($i= 1; $i < 13; $i++){
            $count= Company::when(!empty($status), function($query) use($status){
                $query->where('status', $status);
            })
            ->whereMonth('created_at', Carbon::parse($now->month($i)->format('Y-m')))->count();

            $companies_by_month[]= $count;
        }

        return [
            'companies_by_month' => $companies_by_month,
        ];
    }

    private function getWorkersByMonth($status) : array{
        $now= Carbon::now();

        $workers_by_month= [];

        for($i= 1; $i < 13; $i++){
            $count= Worker::when(!empty($status), function($query) use($status){
                $query->where('status', $status);
            })
            ->whereMonth('created_at', Carbon::parse($now->month($i)->format('Y-m')))->count();

            $workers_by_month[]= $count;
        }

        return [
            'workers_by_month' => $workers_by_month,
        ];
    }

    private function getSubscriptionsByMonth($status) : array{
        $now= Carbon::now();

        $subscriptions_by_month= [];

        for($i= 1; $i < 13; $i++){
            $count= Subscription::when(!empty($status), function($query) use($status){
                $query->where('status', $status);
            })
            ->whereMonth('created_at', Carbon::parse($now->month($i)->format('Y-m')))->count();

            $subscriptions_by_month[]= $count;
        }

        return [
            'subscriptions_by_month' => $subscriptions_by_month,
        ];
    }

    private function getExpiredSubscriptionsByMonth($status) : array{
        $now= Carbon::now();

        $subscriptions_by_month= [];

        for($i= 1; $i < 13; $i++){
            $count= Subscription::when(!empty($status), function($query) use($status){
                $query->where('status', $status);
            })
            ->where('is_active', true)
            ->whereMonth('ending_date', Carbon::parse($now->month($i)->format('Y-m')))->count();

            $subscriptions_by_month[]= $count;
        }

        return [
            'subscriptions_by_month' => $subscriptions_by_month,
        ];
    }
}
