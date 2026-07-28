<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class ProjectsChart extends ChartWidget
{
    protected static ?string $heading = 'Projects';

    protected static ?string $description= 'Growth this year';

    protected static ?int $sort= 3;

    protected function getData(): array
    {
        $data= $this->getProjectsByMonth();

        return [
            'datasets' => [
                [
                    'label' => __('backend.projects'),
                    'data' => $data['projectsPerMonth']
                ]
            ],

            'labels' => $data['months'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function getProjectsByMonth() : array{
        $now= Carbon::now();

        $projectsPerMonth= [];

        for($i= 1; $i < 13; $i++){
            $count= Project::whereMonth('created_at', Carbon::parse($now->month($i)->format('Y-m')))->where('company_id', Auth::user()->company_id)->count();

            $projectsPerMonth[]= $count;
        }

        $months= [
            __('backend.jan'),
            __('backend.feb'),
            __('backend.mar'),
            __('backend.apr'),
            __('backend.may'),
            __('backend.jun'),
            __('backend.jul'),
            __('backend.aug'),
            __('backend.sep'),
            __('backend.oct'),
            __('backend.nov'),
            __('backend.dec'),
        ];

        return [
            'projectsPerMonth' => $projectsPerMonth,
            'months' => $months,
        ];
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('backend.projects');
    }

    public function getDescription(): string|Htmlable|null
    {
        return __('backend.growth_this_year');
    }
}
