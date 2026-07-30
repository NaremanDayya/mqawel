<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contractor;
use App\Models\Item;
use App\Models\Project;
use App\Models\File;
use App\Models\Storage;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Database\Seeder;

class ExpiredFilesReportSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('email', 'nanadaya166@gmail.com')->first();

        if (! $owner) {
            $this->command?->error('User nanadaya166@gmail.com not found — skipping.');

            return;
        }

        $company = Company::find($owner->company_id);

        $storage = Storage::where('company_id', $company->id)->first();
        $project = Project::where('company_id', $company->id)->first();
        $worker = Worker::where('company_id', $company->id)->first();
        $contractor = Contractor::where('company_id', $company->id)->first();
        $item = Item::where('company_id', $company->id)->first();
        $user = User::where('company_id', $company->id)->first() ?? $owner;

        $entries = [
            [
                'name' => 'رخصة مزاولة النشاط التجاري',
                'parent_table' => 'companies',
                'parent_id' => $company->id,
                'category' => 'certificate',
                'expiry_date' => now()->subDays(45),
            ],
            [
                'name' => 'شهادة السلامة المهنية',
                'parent_table' => 'companies',
                'parent_id' => $company->id,
                'category' => 'certificate',
                'expiry_date' => now()->subDays(10),
            ],
            [
                'name' => 'تصريح موقع المشروع',
                'parent_table' => 'projects',
                'parent_id' => $project?->id,
                'category' => 'general',
                'expiry_date' => now()->subDays(120),
            ],
            [
                'name' => 'صورة سير العمل - المشروع',
                'parent_table' => 'projects',
                'parent_id' => $project?->id,
                'category' => 'work_photo',
                'expiry_date' => now()->subDays(5),
            ],
            [
                'name' => 'بطاقة الهوية المدنية - '.($worker?->name ?? 'عامل'),
                'parent_table' => 'workers',
                'parent_id' => $worker?->id,
                'category' => 'certificate',
                'expiry_date' => now()->subDays(30),
            ],
            [
                'name' => 'شهادة فحص المخزن',
                'parent_table' => 'storages',
                'parent_id' => $storage?->id,
                'category' => 'certificate',
                'expiry_date' => now()->subDays(200),
            ],
            [
                'name' => 'ضمان الصنف',
                'parent_table' => 'storage_items',
                'parent_id' => $item?->id,
                'category' => 'general',
                'expiry_date' => now()->subDays(2),
            ],
            [
                'name' => 'شهادة معايرة الصنف',
                'parent_table' => 'items',
                'parent_id' => $item?->id,
                'category' => 'certificate',
                'expiry_date' => now()->subDays(60),
            ],
            [
                'name' => 'عقد مقاول الباطن - '.($contractor?->name ?? 'مقاول'),
                'parent_table' => 'contractors',
                'parent_id' => $contractor?->id,
                'category' => 'general',
                'expiry_date' => now()->subDays(15),
            ],
            [
                'name' => 'رخصة العمل - '.($user?->name ?? 'مستخدم'),
                'parent_table' => 'users',
                'parent_id' => $user?->id,
                'category' => 'certificate',
                'expiry_date' => now()->subDays(90),
            ],
        ];

        foreach ($entries as $entry) {
            if (! $entry['parent_id']) {
                continue;
            }

            File::firstOrCreate(
                ['name' => $entry['name'], 'parent_table' => $entry['parent_table'], 'company_id' => $company->id],
                [
                    'parent_id' => $entry['parent_id'],
                    'file' => 'documents/test-expired-file.pdf',
                    'description' => 'مستند تجريبي منتهي الصلاحية',
                    'category' => $entry['category'],
                    'expiry_date' => $entry['expiry_date'],
                    'is_active' => true,
                    'created_by' => $owner->id,
                ]
            );
        }

        $this->command?->info('Expired files report test data seeded for company: '.$company->name);
    }
}
