<?php

namespace App\Services;

use App\Models\Company;
use App\Models\File;
use App\Models\Project;
use App\Models\User;
use App\Models\Worker;

/**
 * Real, computed profile-readiness numbers for the Company Profile page —
 * every percentage here is derived from actual company data, nothing is
 * hardcoded.
 */
class CompanyProfileInsights
{
    /**
     * @return array<int, array{key: string, label: string, meta: string, percentage: int, color: string}>
     */
    public function completionBreakdown(Company $company): array
    {
        $contactInfo = $this->contactInfoPercentage($company);
        $services = $this->servicesPercentage($company);
        $previousProjects = $this->previousProjectsPercentage($company);
        $certificates = $this->fileCategoryPercentage($company, 'certificate');
        $workPhotos = $this->fileCategoryPercentage($company, 'work_photo');

        return [
            [
                'key' => 'contact_info',
                'label' => __('backend.contact_info_completion'),
                'percentage' => $contactInfo,
                'color' => '#12A594',
            ],
            [
                'key' => 'services',
                'label' => __('backend.services_completion'),
                'percentage' => $services,
                'color' => '#6E56CF',
            ],
            [
                'key' => 'previous_projects',
                'label' => __('backend.previous_projects_completion'),
                'percentage' => $previousProjects,
                'color' => '#3E63DD',
            ],
            [
                'key' => 'certificates',
                'label' => __('backend.certificates_completion'),
                'percentage' => $certificates,
                'color' => '#F5A524',
            ],
            [
                'key' => 'work_photos',
                'label' => __('backend.work_photos_completion'),
                'percentage' => $workPhotos,
                'color' => '#E5484D',
            ],
        ];
    }

    public function overallCompletion(Company $company): int
    {
        $breakdown = $this->completionBreakdown($company);

        if (empty($breakdown)) {
            return 0;
        }

        return (int) round(collect($breakdown)->avg('percentage'));
    }

    /**
     * The lowest-scoring category, used to drive the AI-suggestion banner —
     * plain arithmetic, no API call needed.
     *
     * @return array{key: string, label: string, percentage: int, color: string}|null
     */
    public function weakestCategory(Company $company): ?array
    {
        $breakdown = $this->completionBreakdown($company);

        if (empty($breakdown)) {
            return null;
        }

        return collect($breakdown)->sortBy('percentage')->first();
    }

    public function contactInfoPercentage(Company $company): int
    {
        $fields = [$company->email, $company->phone, $company->website, $company->address];
        $filled = collect($fields)->filter(fn ($value) => filled($value))->count();

        return (int) round(($filled / count($fields)) * 100);
    }

    public function servicesPercentage(Company $company): int
    {
        return min(100, count($company->services ?? []) * 20);
    }

    public function previousProjectsPercentage(Company $company): int
    {
        $count = Project::where('company_id', $company->id)->where('status', 'completed')->count();

        return min(100, $count * 20);
    }

    public function fileCategoryPercentage(Company $company, string $category): int
    {
        $count = File::where('company_id', $company->id)
            ->where('parent_table', 'companies')
            ->where('category', $category)
            ->count();

        return min(100, $count * 25);
    }

    /**
     * @return \Illuminate\Support\Collection<int, Project>
     */
    public function featuredProjects(Company $company, int $limit = 2)
    {
        return Project::where('company_id', $company->id)
            ->where('status', 'completed')
            ->orderByDesc('budget')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array{workers: int, users: int}
     */
    public function staffCounts(Company $company): array
    {
        return [
            'workers' => Worker::where('company_id', $company->id)->where('is_active', true)->count(),
            'users' => User::where('company_id', $company->id)->count(),
        ];
    }
}
