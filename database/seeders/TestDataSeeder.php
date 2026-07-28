<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyRole;
use App\Models\Contact;
use App\Models\Contractor;
use App\Models\DocumentTemplate;
use App\Models\File;
use App\Models\GeneratedDocument;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemDamage;
use App\Models\ItemMovement;
use App\Models\Master;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\ProjectItem;
use App\Models\ProjectStorage;
use App\Models\ProjectUser;
use App\Models\ProjectWorker;
use App\Models\Setting;
use App\Models\Storage;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerPauseDate;
use App\Models\WorkerPayment;
use App\Models\WorkerWorkDay;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $master = Master::firstOrCreate(
                ['email' => 'master.test@mqawel.test'],
                [
                    'name' => 'مسؤول تجريبي',
                    'role' => 'admin',
                    'password' => bcrypt('password'),
                    'is_active' => true,
                ]
            );

            $package = SubscriptionPackage::firstOrCreate(
                ['title' => 'باقة تجريبية كاملة'],
                [
                    'period' => 12,
                    'price' => 500,
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
                ]
            );

            $company = Company::firstOrCreate(
                ['name' => 'شركة الاختبار للمقاولات'],
                [
                    'email' => 'company.test@mqawel.test',
                    'phone' => '99112233',
                    'website' => 'https://test-co.example.com',
                    'business_number' => 'TESTBN0001',
                    'tax_number' => 'TESTTX0001',
                    'activity' => 'مقاولات إنشائية',
                    'is_active' => true,
                    'master_id' => $master->id,
                ]
            );

            Subscription::firstOrCreate(
                ['company_id' => $company->id],
                [
                    'package_id' => $package->id,
                    'period' => 12,
                    'starting_date' => now(),
                    'ending_date' => now()->addYear(),
                    'price' => 500,
                    'currency' => 'OMR',
                    'is_active' => true,
                    'master_id' => $master->id,
                ]
            );

            $adminRole = CompanyRole::firstOrCreate(
                ['company_id' => $company->id, 'name' => 'مسؤول شامل تجريبي'],
                [
                    'can_read_users' => true, 'can_write_users' => true, 'can_edit_users' => true,
                    'can_read_workers' => true, 'can_write_workers' => true, 'can_edit_workers' => true,
                    'can_read_projects' => true, 'can_write_projects' => true, 'can_edit_projects' => true,
                    'can_read_storages' => true, 'can_write_storages' => true, 'can_edit_storages' => true,
                    'can_read_items' => true, 'can_write_items' => true, 'can_edit_items' => true,
                    'can_read_item_categories' => true, 'can_write_item_categories' => true, 'can_edit_item_categories' => true,
                    'can_read_item_movements' => true, 'can_write_item_movements' => true, 'can_edit_item_movements' => true,
                    'can_read_workers_report' => true, 'can_write_workers_report' => true, 'can_edit_workers_report' => true,
                    'can_read_worker_expenses_report' => true, 'can_write_worker_expenses_report' => true, 'can_edit_worker_expenses_report' => true,
                    'can_read_expired_files_report' => true, 'can_write_expired_files_report' => true, 'can_edit_expired_files_report' => true,
                    'can_read_project_expenses_report' => true, 'can_write_project_expenses_report' => true, 'can_edit_project_expenses_report' => true,
                    'can_read_roles' => true, 'can_write_roles' => true, 'can_edit_roles' => true,
                    'can_read_document_creator' => true, 'can_write_document_creator' => true, 'can_edit_document_creator' => true,
                ]
            );
            $adminRole->forceFill([
                'can_read_company_files' => true,
                'can_write_company_files' => true,
                'can_edit_company_files' => true,
            ])->save();

            $viewerRole = CompanyRole::firstOrCreate(
                ['company_id' => $company->id, 'name' => 'عضو محدود تجريبي'],
                [
                    'can_read_users' => true,
                    'can_read_workers' => true,
                    'can_read_projects' => true,
                    'can_read_storages' => true,
                    'can_read_items' => true,
                ]
            );
            $viewerRole->forceFill(['can_read_company_files' => true])->save();

            $owner = User::firstOrCreate(
                ['email' => 'owner.test@mqawel.test'],
                [
                    'company_id' => $company->id,
                    'role_id' => $adminRole->id,
                    'name' => 'أحمد التجريبي',
                    'phone' => '91112222',
                    'job_title' => 'المالك',
                    'password' => bcrypt('password'),
                    'is_active' => true,
                ]
            );

            $staff = User::firstOrCreate(
                ['email' => 'staff.test@mqawel.test'],
                [
                    'company_id' => $company->id,
                    'role_id' => $adminRole->id,
                    'name' => 'سارة التجريبية',
                    'phone' => '91112223',
                    'job_title' => 'مسؤولة مشاريع',
                    'password' => bcrypt('password'),
                    'is_active' => true,
                    'created_by' => $owner->id,
                ]
            );

            User::firstOrCreate(
                ['email' => 'viewer.test@mqawel.test'],
                [
                    'company_id' => $company->id,
                    'role_id' => $viewerRole->id,
                    'name' => 'خالد التجريبي',
                    'phone' => '91112224',
                    'job_title' => 'مشرف موقع',
                    'password' => bcrypt('password'),
                    'is_active' => true,
                    'created_by' => $owner->id,
                ]
            );

            $storages = collect([
                ['name' => 'المخزن الرئيسي', 'address' => 'مسقط، الغبرة'],
                ['name' => 'مخزن الموقع', 'address' => 'نزوى'],
            ])->map(fn ($data) => Storage::firstOrCreate(
                ['company_id' => $company->id, 'name' => $data['name']],
                ['address' => $data['address'], 'is_active' => true, 'created_by' => $owner->id]
            ));

            $categories = collect([
                'أدوات كهربائية', 'مواد بناء', 'معدات السلامة',
            ])->map(fn ($name) => ItemCategory::firstOrCreate(
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

            $contractors = collect([
                'مؤسسة الرمال للمقاولات', 'شركة الخليج للمعدات',
            ])->map(fn ($name) => Contractor::firstOrCreate(
                ['company_id' => $company->id, 'name' => $name],
                [
                    'type' => 'مقاول باطن',
                    'phone' => '99887766',
                    'email' => strtolower(str_replace(' ', '', $name)).'@example.com',
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
                        'living_address' => 'سكن العمال - مسقط',
                        'job_title' => ['حداد', 'نجار', 'سائق', 'عامل بناء'][$i % 4],
                        'job_description' => 'مهام تجريبية للعامل',
                        'is_active' => true,
                        'created_by' => $owner->id,
                    ]
                ));
            }

            $projects = collect([
                ['name' => 'مشروع تجريبي 1', 'address' => 'مسقط', 'status' => 'processing', 'completion_percentage' => 45],
                ['name' => 'مشروع تجريبي 2', 'address' => 'نزوى', 'status' => 'pending', 'completion_percentage' => 10],
            ])->map(fn ($data) => Project::firstOrCreate(
                ['company_id' => $company->id, 'name' => $data['name']],
                [
                    'storage_id' => $storages->first()->id,
                    'description' => 'وصف تجريبي للمشروع',
                    'address' => $data['address'],
                    'budget' => rand(20000, 80000),
                    'currency' => 'OMR',
                    'status' => $data['status'],
                    'completion_percentage' => $data['completion_percentage'],
                    'created_by' => $owner->id,
                ]
            ));

            foreach ($projects as $pi => $project) {
                ProjectUser::firstOrCreate(
                    ['project_id' => $project->id, 'user_id' => $staff->id],
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

            Contact::firstOrCreate(
                ['email' => 'lead.test@example.com'],
                [
                    'name' => 'عميل محتمل تجريبي',
                    'phone' => '92223344',
                    'company_name' => 'شركة عميل تجريبية',
                    'purpose' => 'استفسار عن الخدمة',
                    'title' => 'استفسار تجريبي',
                    'description' => 'رسالة تواصل تجريبية',
                    'is_open' => true,
                ]
            );

            if (Setting::count() === 0) {
                Setting::create([
                    'email_1' => 'info@mqawel.test',
                    'phone_1' => '80000000',
                    'whatsapp' => '91112222',
                ]);
            }
        });

        $this->command?->info('Test data seeded successfully for company: شركة الاختبار للمقاولات (owner login: owner.test@mqawel.test / password)');
    }
}
