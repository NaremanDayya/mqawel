<?php

namespace App\Filament\Pages;

use App\Models\File;
use App\Models\Item;
use App\Models\ItemMovement;
use App\Models\Project;
use App\Models\Worker;
use App\Services\CompanyInsights;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class MyArea extends BaseDashboard
{
    protected static string $view = 'filament.pages.my-area';

    public static function getNavigationLabel(): string
    {
        return __('backend.my_area');
    }

    public static function getNavigationBadge(): ?string
    {
        if (! Auth::check()) {
            return null;
        }

        $companyId = Auth::user()->company_id;
        $expiryDays = Auth::user()->company->about_to_expire_days ?? 30;

        $total = array_sum(app(CompanyInsights::class)->alertCounts($companyId, $expiryDays));

        return $total > 0 ? (string) $total : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public function getTitle(): string|Htmlable
    {
        return __('backend.my_area');
    }

    protected function getHeaderActions(): array
    {
        $pendingActions = array_sum($this->getAlertCounts());

        return [
            Action::make('customize')
                ->label(__('backend.customize'))
                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray')
                ->modalHeading(__('backend.customize_dashboard'))
                ->modalDescription(__('backend.customize_dashboard_desc'))
                ->modalSubmitActionLabel(__('backend.save_view'))
                ->modalCancelActionLabel(__('backend.cancel'))
                ->fillForm(fn () => $this->getPreferences())
                ->form([
                    Checkbox::make('attention')->label(__('backend.pref_attention')),
                    Checkbox::make('kpis')->label(__('backend.pref_kpis')),
                    Checkbox::make('alerts')->label(__('backend.pref_alerts')),
                    Checkbox::make('actions')->label(__('backend.pref_actions')),
                    Checkbox::make('progress')->label(__('backend.pref_progress')),
                    Checkbox::make('activity')->label(__('backend.pref_activity')),
                    Checkbox::make('distribution')->label(__('backend.pref_distribution')),
                ])
                ->action(function (array $data) {
                    Auth::user()->update(['dashboard_preferences' => $data]);
                }),

            Action::make('refresh')
                ->label(__('backend.refresh'))
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn () => null),

            Action::make('recommendations')
                ->label(__('backend.recommendations'))
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning')
                ->badge($pendingActions > 0 ? $pendingActions : null)
                ->url('#alerts-panel'),

            Action::make('manageProjects')
                ->label(__('backend.manage_projects'))
                ->icon('heroicon-o-map-pin')
                ->url(url('company/projects')),
        ];
    }

    public function getPreferences(): array
    {
        $defaults = [
            'attention' => true,
            'kpis' => true,
            'alerts' => true,
            'actions' => true,
            'progress' => true,
            'activity' => true,
            'distribution' => true,
        ];

        return array_merge($defaults, Auth::user()->dashboard_preferences ?? []);
    }

    public function getGreeting(): string
    {
        $greeting = ((int) now()->format('G')) < 12 ? __('backend.good_morning') : __('backend.good_evening');

        return $greeting.' '.Auth::user()->name;
    }

    public function getLastSeen(): ?string
    {
        $lastSeen = session('previous_login_at');

        return $lastSeen ? Carbon::parse($lastSeen)->diffForHumans() : null;
    }

    protected function companyId(): ?int
    {
        return Auth::user()->company_id;
    }

    protected function getAlertCounts(): array
    {
        $expiryDays = Auth::user()->company->about_to_expire_days ?? 30;

        return app(CompanyInsights::class)->alertCounts($this->companyId(), $expiryDays);
    }

    public function getKpis(): array
    {
        $companyId = $this->companyId();
        $alerts = $this->getAlertCounts();

        $processingProject = Project::where('company_id', $companyId)->where('status', 'processing')->first();

        $thisMonthProjects = Project::where('company_id', $companyId)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $lastMonthProjects = Project::where('company_id', $companyId)->whereMonth('created_at', now()->subMonthNoOverflow()->month)->whereYear('created_at', now()->subMonthNoOverflow()->year)->count();

        return [
            'total_projects' => Project::where('company_id', $companyId)->count(),
            'total_projects_trend' => $thisMonthProjects - $lastMonthProjects,
            'processing_projects' => Project::where('company_id', $companyId)->where('status', 'processing')->count(),
            'processing_project_name' => $processingProject?->name,
            'total_workers' => Worker::where('company_id', $companyId)->where('is_active', true)->count(),
            'incomplete_workers' => $alerts['incomplete_workers'],
            'pending_actions' => array_sum($alerts),
        ];
    }

    public function getBannerStats(): array
    {
        $companyId = $this->companyId();

        return [
            'pending_actions' => array_sum($this->getAlertCounts()),
            'workers_under_management' => Worker::where('company_id', $companyId)->where('is_active', true)->count(),
            'active_projects' => Project::where('company_id', $companyId)->whereIn('status', ['pending', 'processing'])->count(),
        ];
    }

    public function getAlerts(): array
    {
        $counts = $this->getAlertCounts();
        $rows = [];

        if ($counts['expired_files'] > 0) {
            $rows[] = [
                'icon' => 'heroicon-o-archive-box-x-mark',
                'tone' => 'danger',
                'title' => __('backend.expired_files'),
                'desc' => __('backend.requires_instant_review'),
                'count' => $counts['expired_files'],
                'url' => url('company/expired-files-report'),
            ];
        }

        if ($counts['incomplete_workers'] > 0) {
            $rows[] = [
                'icon' => 'heroicon-o-briefcase',
                'tone' => 'warning',
                'title' => __('backend.incomplete_workers'),
                'desc' => __('backend.requires_validation'),
                'count' => $counts['incomplete_workers'],
                'url' => url('company/workers'),
            ];
        }

        if ($counts['about_to_expire'] > 0) {
            $rows[] = [
                'icon' => 'heroicon-o-archive-box-arrow-down',
                'tone' => 'warning',
                'title' => __('backend.files_about_to_expire'),
                'desc' => __('backend.requires_observation'),
                'count' => $counts['about_to_expire'],
                'url' => url('company/expired-files-report'),
            ];
        }

        if ($counts['incomplete_files'] > 0) {
            $rows[] = [
                'icon' => 'heroicon-o-archive-box',
                'tone' => 'info',
                'title' => __('backend.incomplete_files'),
                'desc' => __('backend.requires_validation'),
                'count' => $counts['incomplete_files'],
                'url' => url('company/expired-files-report'),
            ];
        }

        return $rows;
    }

    public function getSuggestedActions(): array
    {
        $companyId = $this->companyId();
        $suggestions = [];

        $expiredFile = File::where('company_id', $companyId)->where('expiry_date', '<', date('Y-m-d'))->orderBy('expiry_date')->first();
        if ($expiredFile) {
            $suggestions[] = [
                'tone' => 'danger',
                'message' => __('backend.renew_document_suggestion', ['title' => $expiredFile->name ?? '—']),
                'label' => __('backend.action_view'),
                'url' => url('company/expired-files-report'),
            ];
        }

        $incompleteWorker = Worker::where('company_id', $companyId)->where(function ($q) {
            $q->whereNull('picture')->orWhereNull('name')->orWhereNull('phone')->orWhereNull('ethnicity')->orWhereNull('living_address')->orWhereNull('job_title');
        })->first();
        if ($incompleteWorker) {
            $suggestions[] = [
                'tone' => 'clay',
                'message' => __('backend.complete_worker_suggestion', ['name' => $incompleteWorker->name ?? '—']),
                'label' => __('backend.action_complete'),
                'url' => url('company/workers'),
            ];
        }

        $unassignedItems = Item::where('company_id', $companyId)->whereNull('storage_id')->count();
        if ($unassignedItems > 0) {
            $suggestions[] = [
                'tone' => 'info',
                'message' => __('backend.unassigned_inventory_suggestion', ['count' => $unassignedItems]),
                'label' => __('backend.action_classify'),
                'url' => url('company/items'),
            ];
        }

        return $suggestions;
    }

    public function getRecentActivity(): array
    {
        return ItemMovement::where('company_id', $this->companyId())
            ->with(['item', 'storage', 'createdBy'])
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(function (ItemMovement $movement) {
                $typeLabel = match ($movement->type) {
                    'in' => __('backend.in_to_storage'),
                    'out' => __('backend.out_from_storage'),
                    'adjust' => __('backend.adjust_storage'),
                    default => '',
                };

                return [
                    'type' => $movement->type,
                    'item' => $movement->item->name ?? '—',
                    'storage' => $movement->storage->name ?? '—',
                    'typeLabel' => $typeLabel,
                    'performer' => $movement->createdBy->name ?? null,
                    'time' => $movement->created_at?->diffForHumans(),
                ];
            })
            ->all();
    }

    public function getProjectProgress(): array
    {
        $projects = Project::where('company_id', $this->companyId())
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        if ($projects->isEmpty()) {
            return ['rows' => [], 'average' => null];
        }

        $statusMeta = [
            'pending' => ['label' => __('backend.pending'), 'color' => '#F5A524'],
            'processing' => ['label' => __('backend.processing'), 'color' => '#6E56CF'],
            'completed' => ['label' => __('backend.completed'), 'color' => '#12A594'],
        ];

        $rows = $projects->map(function (Project $project) use ($statusMeta) {
            $meta = $statusMeta[$project->status] ?? ['label' => $project->status, 'color' => '#8891B5'];

            return [
                'name' => $project->name,
                'meta' => $meta['label'],
                'color' => $meta['color'],
                'pct' => (int) $project->completion_percentage,
            ];
        })->all();

        return [
            'rows' => $rows,
            'average' => (int) round($projects->avg('completion_percentage')),
        ];
    }

    public function getInventoryDistribution(): array
    {
        $items = Item::where('company_id', $this->companyId())->where('is_active', true)->with('storage')->get();

        $grouped = $items->groupBy(fn (Item $item) => $item->storage->name ?? __('backend.unclassified'));

        $colors = ['#6E56CF', '#F5A524', '#22C1D6', '#3E63DD', '#12A594', '#E5484D'];
        $result = [];
        $i = 0;

        foreach ($grouped as $name => $group) {
            $result[] = [
                'name' => $name,
                'value' => (float) $group->sum('quantity'),
                'color' => $colors[$i % count($colors)],
            ];
            $i++;
        }

        return $result;
    }
}
