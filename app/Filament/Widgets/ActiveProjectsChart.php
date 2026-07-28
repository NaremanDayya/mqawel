<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use App\Models\Project;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActiveProjectsChart extends ChartWidget
{
    protected static ?string $heading = 'Companies';

    protected static ?string $description= 'Active vs Inactive companies';

    protected static ?int $sort= 4;

    protected function getData(): array
    {
        $data= Project::select('status', DB::raw('count(*) as count'))->where('company_id', Auth::user()->company_id)->groupBy('status')->pluck('count', 'status')->toArray();

        return [
            'datasets' => [
                [
                    'label' => __('backend.projects'),
                    'data' => array_values($data),
                ]
            ],

            'labels' => [__('backend.pending'), __('backend.processing'), __('backend.completed'), __('backend.cancelled')]
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('backend.projects');
    }

    public function getDescription(): string|Htmlable|null
    {
        return __('backend.comparison_between_projects');
    }
}
