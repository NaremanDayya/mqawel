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
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Get;
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

        $company = Auth::user()->company;
        $documentSettings = $company->notification_settings['documents'] ?? [];
        $expiryDays = $documentSettings['lead_time_days'] ?? ($company->about_to_expire_days ?? 30);
        $enabled = $documentSettings['enabled'] ?? true;
        $expiryAlert = $documentSettings['expiry_alert'] ?? true;

        $total = array_sum(app(CompanyInsights::class)->alertCounts(Auth::user()->company_id, $expiryDays, $enabled, $expiryAlert));

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
                    $this->widgetFieldset('attention', __('backend.pref_attention'), [
                        'pending_actions' => __('backend.pending_actions'),
                        'workers_under_management' => __('backend.workers_under_management'),
                        'active_projects' => __('backend.active_projects'),
                    ]),
                    $this->widgetFieldset('kpis', __('backend.pref_kpis'), [
                        'total_projects' => __('backend.total_projects'),
                        'processing_projects' => __('backend.processing_projects'),
                        'total_workers' => __('backend.total_workers'),
                        'active_warnings' => __('backend.active_warnings'),
                    ]),
                    $this->widgetFieldset('alerts', __('backend.pref_alerts'), [
                        'expired_files' => __('backend.expired_files'),
                        'incomplete_workers' => __('backend.incomplete_workers'),
                        'about_to_expire' => __('backend.about_to_expire'),
                        'incomplete_files' => __('backend.incomplete_files'),
                    ]),
                    $this->widgetFieldset('actions', __('backend.pref_actions'), [
                        'renew_document' => __('backend.pref_item_renew_document'),
                        'complete_worker' => __('backend.pref_item_complete_worker'),
                        'unassigned_inventory' => __('backend.pref_item_unassigned_inventory'),
                    ]),
                    $this->widgetFieldset('progress', __('backend.pref_progress'), [
                        'project_rows' => __('backend.pref_item_project_rows'),
                        'overall_average' => __('backend.overall_average_completion'),
                    ]),
                    $this->widgetFieldset('activity', __('backend.pref_activity'), [
                        'in' => __('backend.in_to_storage'),
                        'out' => __('backend.out_from_storage'),
                        'adjust' => __('backend.adjust_storage'),
                    ]),
                    $this->widgetFieldset('distribution', __('backend.pref_distribution'), [
                        'donut' => __('backend.pref_item_donut'),
                        'legend' => __('backend.pref_item_legend'),
                    ]),
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

    protected function widgetFieldset(string $key, string $label, array $items): Fieldset
    {
        return Fieldset::make($label)
            ->schema([
                Checkbox::make("{$key}.enabled")
                    ->label($label)
                    ->live()
                    ->columnSpanFull(),
                Grid::make(2)
                    ->schema(collect($items)
                        ->map(fn ($itemLabel, $itemKey) => Checkbox::make("{$key}.items.{$itemKey}")->label($itemLabel))
                        ->values()
                        ->all())
                    ->visible(fn (Get $get) => (bool) $get("{$key}.enabled"))
                    ->columnSpanFull(),
            ]);
    }

    public function getPreferenceDefaults(): array
    {
        return [
            'attention' => ['enabled' => true, 'items' => ['pending_actions' => true, 'workers_under_management' => true, 'active_projects' => true]],
            'kpis' => ['enabled' => true, 'items' => ['total_projects' => true, 'processing_projects' => true, 'total_workers' => true, 'active_warnings' => true]],
            'alerts' => ['enabled' => true, 'items' => ['expired_files' => true, 'incomplete_workers' => true, 'about_to_expire' => true, 'incomplete_files' => true]],
            'actions' => ['enabled' => true, 'items' => ['renew_document' => true, 'complete_worker' => true, 'unassigned_inventory' => true]],
            'progress' => ['enabled' => true, 'items' => ['project_rows' => true, 'overall_average' => true]],
            'activity' => ['enabled' => true, 'items' => ['in' => true, 'out' => true, 'adjust' => true]],
            'distribution' => ['enabled' => true, 'items' => ['donut' => true, 'legend' => true]],
        ];
    }

    public function getPreferences(): array
    {
        $defaults = $this->getPreferenceDefaults();
        $saved = Auth::user()->dashboard_preferences ?? [];

        foreach ($defaults as $section => $config) {
            $defaults[$section]['enabled'] = $saved[$section]['enabled'] ?? $config['enabled'];

            foreach ($config['items'] as $itemKey => $itemDefault) {
                $defaults[$section]['items'][$itemKey] = $saved[$section]['items'][$itemKey] ?? $itemDefault;
            }
        }

        return $defaults;
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
        $company = Auth::user()->company;
        $documentSettings = $company->notification_settings['documents'] ?? [];
        $expiryDays = $documentSettings['lead_time_days'] ?? ($company->about_to_expire_days ?? 30);
        $enabled = $documentSettings['enabled'] ?? true;
        $expiryAlert = $documentSettings['expiry_alert'] ?? true;

        return app(CompanyInsights::class)->alertCounts($this->companyId(), $expiryDays, $enabled, $expiryAlert);
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
        $enabled = $this->getPreferences()['alerts']['items'];
        $rows = [];

        if ($enabled['expired_files'] && $counts['expired_files'] > 0) {
            $rows[] = [
                'icon' => 'heroicon-o-archive-box-x-mark',
                'tone' => 'danger',
                'title' => __('backend.expired_files'),
                'desc' => __('backend.requires_instant_review'),
                'count' => $counts['expired_files'],
                'url' => url('company/expired-files-report'),
            ];
        }

        if ($enabled['incomplete_workers'] && $counts['incomplete_workers'] > 0) {
            $rows[] = [
                'icon' => 'heroicon-o-briefcase',
                'tone' => 'warning',
                'title' => __('backend.incomplete_workers'),
                'desc' => __('backend.requires_validation'),
                'count' => $counts['incomplete_workers'],
                'url' => url('company/workers'),
            ];
        }

        if ($enabled['about_to_expire'] && $counts['about_to_expire'] > 0) {
            $rows[] = [
                'icon' => 'heroicon-o-archive-box-arrow-down',
                'tone' => 'warning',
                'title' => __('backend.files_about_to_expire'),
                'desc' => __('backend.requires_observation'),
                'count' => $counts['about_to_expire'],
                'url' => url('company/expired-files-report'),
            ];
        }

        if ($enabled['incomplete_files'] && $counts['incomplete_files'] > 0) {
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
        $enabled = $this->getPreferences()['actions']['items'];
        $suggestions = [];

        $expiredFile = $enabled['renew_document']
            ? File::where('company_id', $companyId)->where('expiry_date', '<', date('Y-m-d'))->orderBy('expiry_date')->first()
            : null;
        if ($expiredFile) {
            $suggestions[] = [
                'tone' => 'danger',
                'message' => __('backend.renew_document_suggestion', ['title' => $expiredFile->name ?? '—']),
                'label' => __('backend.action_view'),
                'url' => url('company/expired-files-report'),
            ];
        }

        $incompleteWorker = $enabled['complete_worker']
            ? Worker::where('company_id', $companyId)->where(function ($q) {
                $q->whereNull('picture')->orWhereNull('name')->orWhereNull('phone')->orWhereNull('ethnicity')->orWhereNull('living_address')->orWhereNull('job_title');
            })->first()
            : null;
        if ($incompleteWorker) {
            $suggestions[] = [
                'tone' => 'clay',
                'message' => __('backend.complete_worker_suggestion', ['name' => $incompleteWorker->name ?? '—']),
                'label' => __('backend.action_complete'),
                'url' => url('company/workers'),
            ];
        }

        $unassignedItems = $enabled['unassigned_inventory'] ? Item::where('company_id', $companyId)->whereNull('storage_id')->count() : 0;
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
        $enabledTypes = array_keys(array_filter($this->getPreferences()['activity']['items']));

        if (empty($enabledTypes)) {
            return [];
        }

        return ItemMovement::where('company_id', $this->companyId())
            ->whereIn('type', $enabledTypes)
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
