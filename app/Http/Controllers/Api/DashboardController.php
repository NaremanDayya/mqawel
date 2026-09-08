<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Item;
use App\Models\ItemMovement;
use App\Models\Project;
use App\Models\Worker;
use App\Services\CompanyInsights;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private const PREFERENCE_DEFAULTS = [
        'attention' => ['enabled' => true, 'items' => ['pending_actions' => true, 'workers_under_management' => true, 'active_projects' => true]],
        'kpis' => ['enabled' => true, 'items' => ['total_projects' => true, 'processing_projects' => true, 'total_workers' => true, 'active_warnings' => true]],
        'alerts' => ['enabled' => true, 'items' => ['expired_files' => true, 'incomplete_workers' => true, 'about_to_expire' => true, 'incomplete_files' => true]],
        'actions' => ['enabled' => true, 'items' => ['renew_document' => true, 'complete_worker' => true, 'unassigned_inventory' => true]],
        'progress' => ['enabled' => true, 'items' => ['project_rows' => true, 'overall_average' => true]],
        'activity' => ['enabled' => true, 'items' => ['in' => true, 'out' => true, 'adjust' => true]],
        'distribution' => ['enabled' => true, 'items' => ['donut' => true, 'legend' => true]],
    ];

    public function index(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        $company = $user->company;
        $preferences = $this->preferences($user);
        $insights = app(CompanyInsights::class);

        $documentSettings = $company->notification_settings['documents'] ?? [];
        $expiryDays = $documentSettings['lead_time_days'] ?? ($company->about_to_expire_days ?? 30);
        $alertsEnabled = $documentSettings['enabled'] ?? true;
        $expiryAlertEnabled = $documentSettings['expiry_alert'] ?? true;

        $alerts = $insights->alertCounts($companyId, $expiryDays, $alertsEnabled, $expiryAlertEnabled);

        return response()->json([
            'data' => [
                'banner' => [
                    'pending_actions' => array_sum($alerts),
                    'workers_under_management' => Worker::where('company_id', $companyId)->where('is_active', true)->count(),
                    'active_projects' => Project::where('company_id', $companyId)->whereIn('status', ['pending', 'processing'])->count(),
                ],
                'kpis' => $this->kpis($companyId, $alerts),
                'alerts' => $this->alerts($alerts, $preferences['alerts']['items']),
                'suggested_actions' => $this->suggestedActions($companyId, $preferences['actions']['items']),
                'recent_activity' => $this->recentActivity($companyId, $preferences['activity']['items']),
                'project_progress' => $this->projectProgress($companyId),
                'inventory_distribution' => $this->inventoryDistribution($companyId),
                'preferences' => $preferences,
            ],
        ]);
    }

    private function kpis(int $companyId, array $alerts): array
    {
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

    private function alerts(array $counts, array $enabled): array
    {
        $rows = [];

        if (($enabled['expired_files'] ?? true) && $counts['expired_files'] > 0) {
            $rows[] = ['key' => 'expired_files', 'tone' => 'danger', 'count' => $counts['expired_files']];
        }
        if (($enabled['incomplete_workers'] ?? true) && $counts['incomplete_workers'] > 0) {
            $rows[] = ['key' => 'incomplete_workers', 'tone' => 'warning', 'count' => $counts['incomplete_workers']];
        }
        if (($enabled['about_to_expire'] ?? true) && $counts['about_to_expire'] > 0) {
            $rows[] = ['key' => 'about_to_expire', 'tone' => 'warning', 'count' => $counts['about_to_expire']];
        }
        if (($enabled['incomplete_files'] ?? true) && $counts['incomplete_files'] > 0) {
            $rows[] = ['key' => 'incomplete_files', 'tone' => 'info', 'count' => $counts['incomplete_files']];
        }

        return $rows;
    }

    private function suggestedActions(int $companyId, array $enabled): array
    {
        $suggestions = [];

        $expiredFile = ($enabled['renew_document'] ?? true)
            ? File::where('company_id', $companyId)->where('expiry_date', '<', date('Y-m-d'))->orderBy('expiry_date')->first()
            : null;
        if ($expiredFile) {
            $suggestions[] = ['key' => 'renew_document', 'tone' => 'danger', 'subject' => $expiredFile->name];
        }

        $incompleteWorker = ($enabled['complete_worker'] ?? true)
            ? Worker::where('company_id', $companyId)->where(function ($q) {
                $q->whereNull('picture')->orWhereNull('name')->orWhereNull('phone')->orWhereNull('ethnicity')->orWhereNull('living_address')->orWhereNull('job_title');
            })->first()
            : null;
        if ($incompleteWorker) {
            $suggestions[] = ['key' => 'complete_worker', 'tone' => 'clay', 'subject' => $incompleteWorker->name];
        }

        $unassignedItems = ($enabled['unassigned_inventory'] ?? true) ? Item::where('company_id', $companyId)->whereNull('storage_id')->count() : 0;
        if ($unassignedItems > 0) {
            $suggestions[] = ['key' => 'unassigned_inventory', 'tone' => 'info', 'count' => $unassignedItems];
        }

        return $suggestions;
    }

    private function recentActivity(int $companyId, array $enabled): array
    {
        $enabledTypes = array_keys(array_filter($enabled));

        if (empty($enabledTypes)) {
            return [];
        }

        return ItemMovement::where('company_id', $companyId)
            ->whereIn('type', $enabledTypes)
            ->with(['item', 'storage', 'createdBy'])
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (ItemMovement $movement) => [
                'type' => $movement->type,
                'item' => $movement->item->name ?? null,
                'storage' => $movement->storage->name ?? null,
                'performer' => $movement->createdBy->name ?? null,
                'time' => $movement->created_at?->toIso8601String(),
            ])
            ->all();
    }

    private function projectProgress(int $companyId): array
    {
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        if ($projects->isEmpty()) {
            return ['rows' => [], 'average' => null];
        }

        return [
            'rows' => $projects->map(fn (Project $project) => [
                'id' => $project->id,
                'name' => $project->name,
                'status' => $project->status,
                'completion_percentage' => (int) $project->completion_percentage,
            ])->all(),
            'average' => (int) round($projects->avg('completion_percentage')),
        ];
    }

    private function inventoryDistribution(int $companyId): array
    {
        $items = Item::where('company_id', $companyId)->where('is_active', true)->with('storage')->get();

        $grouped = $items->groupBy(fn (Item $item) => $item->storage->name ?? __('backend.unclassified'));

        return $grouped->map(fn ($group, $name) => [
            'name' => $name,
            'value' => (float) $group->sum('quantity'),
        ])->values()->all();
    }

    private function preferences($user): array
    {
        $defaults = self::PREFERENCE_DEFAULTS;
        $saved = $user->dashboard_preferences ?? [];

        foreach ($defaults as $section => $config) {
            $defaults[$section]['enabled'] = $saved[$section]['enabled'] ?? $config['enabled'];

            foreach ($config['items'] as $itemKey => $itemDefault) {
                $defaults[$section]['items'][$itemKey] = $saved[$section]['items'][$itemKey] ?? $itemDefault;
            }
        }

        return $defaults;
    }
}
