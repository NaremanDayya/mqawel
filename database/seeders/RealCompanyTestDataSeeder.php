<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contractor;
use App\Models\DocumentTemplate;
use App\Models\File;
use App\Models\GeneratedDocument;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemDamage;
use App\Models\ItemMovement;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\ProjectItem;
use App\Models\ProjectStorage;
use App\Models\ProjectUser;
use App\Models\ProjectWorker;
use App\Models\Storage;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerPauseDate;
use App\Models\WorkerPayment;
use App\Models\WorkerWorkDay;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RealCompanyTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('email', 'nanadaya166@gmail.com')->first();

        if (! $owner) {
            $this->command?->error('User nanadaya166@gmail.com not found — skipping.');

            return;
        }

        $company = Company::find($owner->company_id);

        DB::transaction(function () use ($owner, $company) {
            $storages = collect([
                ['name' => 'المخزن الرئيسي', 'address' => 'مسقط'],
                ['name' => 'مخزن الموقع', 'address' => 'صلالة'],
            ])->map(fn ($data) => Storage::firstOrCreate(
                ['company_id' => $company->id, 'name' => $data['name']],
                ['address' => $data['address'], 'is_active' => true, 'created_by' => $owner->id]
            ));

            $categories = collect(['أدوات كهربائية', 'مواد بناء', 'معدات السلامة'])
                ->map(fn ($name) => ItemCategory::firstOrCreate(
                    ['company_id' => $company->id, 'name' => $name],
                    ['is_active' => true, 'created_by' => $owner->id]
                ));

            $items = collect();
            foreach ($categories as $i => $category) {
                for ($j = 1; $j <= 2; $j++) {
                    $items->push(Item::firstOrCreate(
                        ['company_id' => $company->id, 'name' => $category->name.' - صنف '.$j],
                        [
                            'storage_id' => $storages[$i % $storages->count()]->id,
                            'category_id' => $category->id,
                            'description' => 'وصف تجريبي للصنف',
                            'quantity' => rand(5, 100),
                            'status' => 'new',
                            'is_active' => true,
                            'created_by' => $owner->id,
                        ]
                    ));
                }
            }

            $contractors = collect(['مؤسسة الرمال للمقاولات', 'شركة الخليج للمعدات'])
                ->map(fn ($name) => Contractor::firstOrCreate(
                    ['company_id' => $company->id, 'name' => $name],
                    [
                        'type' => 'مقاول باطن',
                        'phone' => '99887766',
                        'address' => 'مسقط',
                        'is_active' => true,
                        'created_by' => $owner->id,
                    ]
                ));

            $workerNames = ['محمد رحيم', 'جون دي كروز', 'رحمن شيخ', 'علي حسين'];
            $nationalities = ['هندي', 'باكستاني', 'بنغلاديشي', 'عماني'];
            $workers = collect();
            foreach ($workerNames as $i => $name) {
                $workers->push(Worker::firstOrCreate(
                    ['company_id' => $company->id, 'name' => $name],
                    [
                        'phone' => '9'.rand(1000000, 9999999),
                        'ethnicity' => $nationalities[$i % count($nationalities)],
                        'living_address' => 'سكن العمال',
                        'job_title' => ['حداد', 'نجار', 'سائق', 'عامل بناء'][$i % 4],
                        'job_description' => 'مهام تجريبية للعامل',
                        'is_active' => true,
                        'created_by' => $owner->id,
                    ]
                ));
            }

            $existingProject = Project::where('company_id', $company->id)->first();

            $newProject = Project::firstOrCreate(
                ['company_id' => $company->id, 'name' => 'مشروع تجريبي إضافي'],
                [
                    'storage_id' => $storages->first()->id,
                    'description' => 'وصف تجريبي للمشروع',
                    'address' => 'مسقط',
                    'budget' => rand(20000, 80000),
                    'currency' => 'OMR',
                    'status' => 'processing',
                    'completion_percentage' => 35,
                    'created_by' => $owner->id,
                ]
            );

            $projects = collect([$existingProject, $newProject])->filter();

            foreach ($projects as $pi => $project) {
                ProjectUser::firstOrCreate(
                    ['project_id' => $project->id, 'user_id' => $owner->id],
                    ['company_id' => $company->id, 'date' => now(), 'created_by' => $owner->id]
                );

                ProjectWorker::firstOrCreate(
                    ['project_id' => $project->id, 'worker_id' => $workers[$pi % $workers->count()]->id],
                    ['company_id' => $company->id, 'date' => now(), 'created_by' => $owner->id]
                );

                ProjectStorage::firstOrCreate(
                    ['project_id' => $project->id, 'storage_id' => $storages[$pi % $storages->count()]->id],
                    ['company_id' => $company->id, 'created_by' => $owner->id]
                );

                $projectItem = ProjectItem::firstOrCreate(
                    ['project_id' => $project->id, 'company_id' => $company->id],
                    ['quantity' => rand(1, 10), 'date' => now(), 'created_by' => $owner->id]
                );
                $projectItem->forceFill(['item_id' => $items[$pi % $items->count()]->id])->save();

                ProjectExpense::firstOrCreate(
                    ['project_id' => $project->id, 'title' => 'مصروف تجريبي - '.$project->name],
                    [
                        'company_id' => $company->id,
                        'description' => 'وصف مصروف تجريبي',
                        'amount' => rand(500, 5000),
                        'currency' => 'OMR',
                        'date' => now()->toDateString(),
                        'created_by' => $owner->id,
                    ]
                );

                ItemMovement::firstOrCreate(
                    ['project_id' => $project->id, 'item_id' => $items[$pi % $items->count()]->id, 'type' => 'out'],
                    [
                        'company_id' => $company->id,
                        'storage_id' => $storages->first()->id,
                        'quantity' => rand(1, 5),
                        'previous_storage_quantity' => 20,
                        'new_storage_quantity' => 15,
                        'movement_date' => now()->toDateString(),
                        'notes' => 'حركة صرف تجريبية',
                        'address' => $project->address,
                        'created_by' => $owner->id,
                    ]
                );
            }

            ItemDamage::firstOrCreate(
                ['item_id' => $items->first()->id, 'responsible_id' => $workers->first()->id],
                [
                    'company_id' => $company->id,
                    'storage_id' => $storages->first()->id,
                    'quantity' => 1,
                    'damage_date' => now()->toDateString(),
                    'notes' => 'ضرر تجريبي أثناء النقل',
                    'created_by' => $owner->id,
                ]
            );

            foreach ($workers as $worker) {
                WorkerPauseDate::firstOrCreate(
                    ['worker_id' => $worker->id, 'date_of_pause' => now()->subDays(3)->toDateString()],
                    ['company_id' => $company->id, 'created_by' => $owner->id]
                );

                for ($d = 1; $d <= 3; $d++) {
                    WorkerWorkDay::firstOrCreate(
                        ['worker_id' => $worker->id, 'day' => now()->subDays($d)->toDateString()],
                        ['company_id' => $company->id, 'created_by' => $owner->id]
                    );
                }

                WorkerPayment::firstOrCreate(
                    ['worker_id' => $worker->id, 'title' => 'راتب شهري تجريبي'],
                    [
                        'company_id' => $company->id,
                        'amount' => rand(300, 600),
                        'currency' => 'OMR',
                        'payment_date' => now()->toDateString(),
                        'payment_method' => 'تحويل بنكي',
                        'status' => 'paid',
                        'created_by' => $owner->id,
                    ]
                );
            }

            File::firstOrCreate(
                ['name' => 'السجل التجاري - تجريبي', 'parent_table' => 'companies'],
                [
                    'company_id' => $company->id,
                    'parent_id' => $company->id,
                    'file' => 'documents/test-cr.pdf',
                    'expiry_date' => now()->addYear(),
                    'is_active' => true,
                    'created_by' => $owner->id,
                ]
            );

            foreach ($projects as $project) {
                File::firstOrCreate(
                    ['name' => 'مخطط الموقع - '.$project->name, 'parent_table' => 'projects'],
                    [
                        'company_id' => $company->id,
                        'parent_id' => $project->id,
                        'file' => 'documents/test-plan.pdf',
                        'expiry_date' => now()->addMonths(6),
                        'is_active' => true,
                        'created_by' => $owner->id,
                    ]
                );
            }

            File::firstOrCreate(
                ['name' => 'بطاقة الهوية - '.$workers->first()->name, 'parent_table' => 'workers'],
                [
                    'company_id' => $company->id,
                    'parent_id' => $workers->first()->id,
                    'file' => 'documents/test-id.pdf',
                    'expiry_date' => now()->addMonths(3),
                    'is_active' => true,
                    'created_by' => $owner->id,
                ]
            );

            $template = DocumentTemplate::where('name', 'عقد مقاول باطن')->first();

            GeneratedDocument::firstOrCreate(
                ['company_id' => $company->id, 'name' => 'عقد مقاول باطن - تجريبي'],
                [
                    'template_id' => $template?->id,
                    'project_id' => $projects->first()->id,
                    'category' => 'contracts',
                    'related_party' => $contractors->first()->name,
                    'details' => 'تفاصيل تجريبية للعقد',
                    'status' => 'draft',
                    'value' => 12500,
                    'created_by' => $owner->id,
                ]
            );

            GeneratedDocument::firstOrCreate(
                ['company_id' => $company->id, 'name' => 'عرض سعر - تجريبي'],
                [
                    'category' => 'quotes',
                    'project_id' => $projects->last()->id,
                    'related_party' => $contractors->last()->name,
                    'status' => 'sent',
                    'value' => 8200,
                    'created_by' => $owner->id,
                ]
            );

            GeneratedDocument::firstOrCreate(
                ['company_id' => $company->id, 'name' => 'خطاب مطالبة مالية - تجريبي'],
                [
                    'category' => 'letters',
                    'project_id' => $projects->first()->id,
                    'related_party' => $contractors->first()->name,
                    'status' => 'signed',
                    'value' => 4200,
                    'created_by' => $owner->id,
                ]
            );
        });

        $this->command?->info('Test data seeded for company: '.$company->name.' (nanadaya166@gmail.com)');
    }
}
