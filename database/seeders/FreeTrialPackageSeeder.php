<?php

namespace Database\Seeders;

use App\Models\Master;
use App\Models\SubscriptionPackage;
use Illuminate\Database\Seeder;

class FreeTrialPackageSeeder extends Seeder
{
    /**
     * Create the free trial package once (14 days, every feature). Safe to run again: if any
     * trial package exists — even one the admin renamed or switched off — nothing changes.
     *
     *   php artisan db:seed --class=FreeTrialPackageSeeder
     */
    public function run(): void
    {
        if (SubscriptionPackage::where('is_trial', true)->exists()) {
            return;
        }

        $master = Master::orderBy('id')->first();

        if (! $master) {
            $this->command?->warn('No admin (master) exists yet, so the trial package was not created.');

            return;
        }

        SubscriptionPackage::create([
            'title' => 'التجربة المجانية',
            'description' => 'جرّب مقاول+ مجاناً بكامل المميزات قبل الاشتراك.',
            'is_trial' => true,
            'trial_days' => 14,
            'currency' => 'OMR',
            'has_workers' => true,
            'has_projects' => true,
            'has_storages' => true,
            'has_items' => true,
            'has_item_categories' => true,
            'has_item_movements' => true,
            'has_workers_report' => true,
            'has_worker_expenses_report' => true,
            'has_expired_files_report' => true,
            'has_project_expenses_report' => true,
            'is_active' => true,
            'master_id' => $master->id,
        ]);
    }
}
