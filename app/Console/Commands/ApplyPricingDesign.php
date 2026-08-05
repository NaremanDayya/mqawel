<?php

namespace App\Console\Commands;

use App\Models\SubscriptionPackage;
use Illuminate\Console\Command;

class ApplyPricingDesign extends Command
{
    protected $signature = 'packages:apply-pricing-design';

    protected $description = 'Populate the 3 lowest-priced active subscription packages with the approved pricing-card content (price, limits, bullets, CTA text, cost comparison)';

    public function handle(): int
    {
        $packages = SubscriptionPackage::where('is_active', true)->orderBy('price')->take(3)->get();

        if ($packages->count() < 3) {
            $this->error('Need at least 3 active packages, found '.$packages->count().'.');

            return self::FAILURE;
        }

        $tiers = [
            [
                'price' => 5.00,
                'max_projects' => 1,
                'max_workers' => 10,
                'description' => 'مشروع واحد وحتى 10 عمال — مناسبة لمن يبدأ الآن.',
                'feature_bullets' => [
                    ['text' => 'إدارة المخزون'],
                    ['text' => 'تقارير أساسية'],
                    ['text' => 'دعم واتساب'],
                ],
                'cta_label' => 'ابدأ مجاناً',
                'traditional_cost_items' => [
                    ['label' => 'إيجار مكتب', 'value' => 100],
                    ['label' => 'راتب منسق', 'value' => 400],
                    ['label' => 'تدريب ومتابعة', 'value' => 50],
                ],
                'savings_note' => 'نفس المهام بدقة أعلى',
            ],
            [
                'price' => 10.00,
                'max_projects' => 5,
                'max_workers' => 50,
                'description' => 'حتى 5 مشاريع و50 عاملاً — الخيار الأنسب لأغلب المقاولين.',
                'feature_bullets' => [
                    ['text' => 'إدارة المخزون الكاملة'],
                    ['text' => 'تقارير مالية متقدمة'],
                    ['text' => 'واتساب مدمج + دعم أولوية'],
                ],
                'cta_label' => 'ترقية الآن',
                'traditional_cost_items' => [
                    ['label' => 'إيجار مكتب', 'value' => 100],
                    ['label' => 'منسقان للمواقع', 'value' => 800],
                    ['label' => 'تدريب ومتابعة', 'value' => 100],
                ],
                'savings_note' => 'ومتابعة لحظية لكل موقع',
            ],
            [
                'price' => 15.00,
                'max_projects' => null,
                'max_workers' => null,
                'description' => 'مشاريع وعمال بلا حدود — لشركات المقاولات الكبيرة.',
                'feature_bullets' => [
                    ['text' => 'جميع ميزات المتقدمة'],
                    ['text' => 'تقارير مخصصة + مدير حساب'],
                    ['text' => 'تدريب الفريق مجاناً'],
                ],
                'cta_label' => 'تواصل معنا',
                'traditional_cost_items' => [
                    ['label' => 'مكتب إداري', 'value' => 150],
                    ['label' => 'فريق تنسيق ومحاسبة', 'value' => 1200],
                    ['label' => 'تدريب وتقارير', 'value' => 150],
                ],
                'savings_note' => 'وتقارير جاهزة بلا انتظار',
            ],
        ];

        foreach ($packages as $index => $package) {
            $package->update($tiers[$index]);
            $this->info("Updated [{$package->id}] {$package->title}: {$tiers[$index]['price']} OMR/month");
        }

        return self::SUCCESS;
    }
}
