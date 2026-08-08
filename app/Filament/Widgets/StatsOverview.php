<?php

namespace App\Filament\Widgets;

use App\Filament\Concerns\HasWidgetVisibility;
use App\Models\Item;
use App\Models\Project;
use App\Models\Storage;
use App\Models\User;
use App\Models\Worker;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StatsOverview extends BaseWidget
{
    use HasWidgetVisibility;

    protected static ?string $pollingInterval= '0s';

    protected static bool $isLazy= true;

    protected static ?int $sort= 1;

    protected function getStats(): array
    {
        return [
            Stat::make(__('backend.total_projects'), Project::where('company_id', Auth::user()->company_id)->count())
            ->description(__('backend.growth_indicator'))
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success')
            ->chart($this->getProjectsByMonth('')['projectsPerMonth'])
            ->url(url: url('company/projects'), shouldOpenInNewTab:true),
            
            Stat::make(__('backend.pending_projects'), Project::where('company_id', Auth::user()->company_id)->where(['status' => 'pending'])->count())
            ->description(__('backend.growth_indicator'))
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('warning')
            ->chart($this->getProjectsByMonth('pending')['projectsPerMonth'])
            ->url(url: url('company/projects?tableFilters[status][value]=pending'), shouldOpenInNewTab:true),

            Stat::make(__('backend.processing_projects'), Project::where('company_id', Auth::user()->company_id)->where(['status' => 'processing'])->count())
            ->description(__('backend.growth_indicator'))
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('primary')
            ->chart($this->getProjectsByMonth('processing')['projectsPerMonth'])
            ->url(url: url('company/projects?tableFilters[status][value]=processing'), shouldOpenInNewTab:true),

            Stat::make(__('backend.completed_projects'), Project::where('company_id', Auth::user()->company_id)->where(['status' => 'completed'])->count())
            ->description(__('backend.growth_indicator'))
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success')
            ->chart($this->getProjectsByMonth('completed')['projectsPerMonth'])
            ->url(url: url('company/projects?tableFilters[status][value]=completed'), shouldOpenInNewTab:true),

            Stat::make(__('backend.total_storages'), Storage::where('company_id', Auth::user()->company_id)->where(['is_active' => true])->count())
            ->url(url: url('company/storages'), shouldOpenInNewTab:true),

            Stat::make(__('backend.total_items'), Item::where('company_id', Auth::user()->company_id)->where(['is_active' => true])->count())
            ->url(url: url('company/items'), shouldOpenInNewTab:true),

            Stat::make(__('backend.total_workers'), Worker::where('company_id', Auth::user()->company_id)->where(['is_active' => true])->count())
            ->url(url: url('company/workers'), shouldOpenInNewTab:true),

            Stat::make(__('backend.total_users'), User::where('company_id', Auth::user()->company_id)->where(['is_active' => true])->count())
            ->url(url: url('company/users'), shouldOpenInNewTab:true),
        ];
    }

    private function getProjectsByMonth($status) : array{
        $now= Carbon::now();

        $projectsPerMonth= [];

        for($i= 1; $i < 13; $i++){
            $count= Project::when(!empty($status), function($query) use($status){
                $query->where('status', $status);
            })
            ->where('company_id', Auth::user()->company_id)
            ->whereMonth('created_at', Carbon::parse($now->month($i)->format('Y-m')))->count();

            $projectsPerMonth[]= $count;
        }

        return [
            'projectsPerMonth' => $projectsPerMonth,
        ];
    }

    /*protected function getHeading(): ?string
    {
        return __('backend.analytics');
    }*/
}
