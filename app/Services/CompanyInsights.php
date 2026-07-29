<?php

namespace App\Services;

use App\Models\File;
use App\Models\Project;
use App\Models\Worker;
use Illuminate\Support\Carbon;

/**
 * Shared read-only queries about a company's data, used by the My Area
 * dashboard and the in-app AI assistant so both stay in sync.
 */
class CompanyInsights
{
    /**
     * @return array{expired_files: int, about_to_expire: int, incomplete_files: int, incomplete_workers: int}
     */
    public function alertCounts(int $companyId, int $expiryDays = 30, bool $documentAlertsEnabled = true, bool $expiryAlertEnabled = true): array
    {
        return [
            'expired_files' => $documentAlertsEnabled
                ? File::where('company_id', $companyId)->where('expiry_date', '<', date('Y-m-d'))->count()
                : 0,
            'about_to_expire' => ($documentAlertsEnabled && $expiryAlertEnabled)
                ? File::where('company_id', $companyId)->where('expiry_date', '<=', Carbon::now()->addDays($expiryDays))->where('expiry_date', '>', Carbon::now())->count()
                : 0,
            'incomplete_files' => File::where('company_id', $companyId)->where(function ($q) {
                $q->whereNull('name')->orWhereNull('file')->orWhereNull('expiry_date');
            })->count(),
            'incomplete_workers' => Worker::where('company_id', $companyId)->where(function ($q) {
                $q->whereNull('picture')->orWhereNull('name')->orWhereNull('phone')->orWhereNull('ethnicity')->orWhereNull('living_address')->orWhereNull('job_title');
            })->count(),
        ];
    }

    /**
     * @return array{total: int, by_status: array<string, int>, active_projects: array<int, string>}
     */
    public function projectsSummary(int $companyId): array
    {
        return [
            'total' => Project::where('company_id', $companyId)->count(),
            'by_status' => Project::where('company_id', $companyId)
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'active_projects' => Project::where('company_id', $companyId)
                ->whereIn('status', ['pending', 'processing'])
                ->pluck('name')
                ->toArray(),
        ];
    }

    /**
     * @return array{name: string, status: string, address: ?string, budget: ?string, completion_percentage: int}|null
     */
    public function projectDetails(int $companyId, string $query): ?array
    {
        $project = Project::where('company_id', $companyId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");

                if (is_numeric($query)) {
                    $q->orWhere('id', (int) $query);
                }
            })
            ->first();

        if (! $project) {
            return null;
        }

        return [
            'name' => $project->name,
            'status' => $project->status,
            'address' => $project->address,
            'budget' => $project->budget,
            'completion_percentage' => (int) $project->completion_percentage,
        ];
    }

    /**
     * @return array<int, array{name: string, expiry_date: ?string, type: ?string}>
     */
    public function expiringDocuments(int $companyId, int $days = 30): array
    {
        return File::where('company_id', $companyId)
            ->where('expiry_date', '<=', now()->addDays($days))
            ->orderBy('expiry_date')
            ->limit(20)
            ->get(['name', 'expiry_date', 'parent_table'])
            ->map(fn (File $file) => [
                'name' => $file->name,
                'expiry_date' => optional($file->expiry_date)->format('Y-m-d'),
                'type' => $file->parent_table,
            ])
            ->all();
    }
}
