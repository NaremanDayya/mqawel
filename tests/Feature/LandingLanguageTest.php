<?php

namespace Tests\Feature;

use App\Models\SubscriptionPackage;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The language switch on the Mqawel+ landing page: Arabic by default, English on request, and
 * every piece of the page's own text comes from lang/ar|en/landing.php.
 */
class LandingLanguageTest extends TestCase
{
    /** @return array<string, string> */
    private function strings(string $lang): array
    {
        return require lang_path("$lang/landing.php");
    }

    public function test_the_landing_page_is_arabic_by_default_and_offers_english(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('lang="ar"', false)
            ->assertSee('dir="rtl"', false)
            ->assertSee('ابدأ في 3 خطوات بسيطة')
            ->assertSee(url('lang/en'), false)   // the switch points at the OTHER language
            ->assertSee('English');
    }

    public function test_choosing_english_translates_the_whole_page_and_offers_arabic(): void
    {
        $this->get('/lang/en')->assertRedirect();

        $html = $this->get('/')->assertOk()
            ->assertSee('lang="en"', false)
            ->assertSee('dir="ltr"', false)
            ->assertSee('Start in 3 simple steps')
            ->assertSee('Contractor reviews')
            ->assertSee('Is my data safe in the cloud?')
            ->assertSee('Abu Khalid')
            ->assertSee(url('lang/ar'), false)
            ->assertSee('العربية')
            ->getContent();

        // None of the page's own Arabic sentences is left behind (short labels and the switch itself excepted).
        foreach ($this->strings('ar') as $key => $arabic) {
            if (str_starts_with($key, 'switch_') || mb_strlen($arabic) < 12) {
                continue;
            }

            $this->assertStringNotContainsString($arabic, $html, "Arabic text still shown in English mode: $key");
        }
    }

    public function test_the_choice_can_be_switched_back_to_arabic(): void
    {
        $this->get('/lang/en');
        $this->get('/lang/ar')->assertRedirect();

        $this->get('/')->assertOk()->assertSee('dir="rtl"', false)->assertSee('ابدأ في 3 خطوات بسيطة')->assertDontSee('Start in 3 simple steps');
    }

    public function test_only_arabic_and_english_exist(): void
    {
        $this->get('/lang/fr')->assertNotFound();
        $this->get('/lang/EN')->assertNotFound();

        $this->get('/')->assertOk()->assertSee('dir="rtl"', false);   // still the default
    }

    public function test_both_languages_have_the_same_keys_and_no_empty_text(): void
    {
        $ar = $this->strings('ar');
        $en = $this->strings('en');

        $this->assertSame(array_keys($ar), array_keys($en));

        foreach ([$ar, $en] as $strings) {
            foreach ($strings as $key => $text) {
                $this->assertNotSame('', trim($text), "empty text for $key");
            }
        }

        // The English file is English apart from the switch back to Arabic.
        foreach ($en as $key => $text) {
            if (! str_starts_with($key, 'switch_')) {
                $this->assertDoesNotMatchRegularExpression('/[\x{0600}-\x{06FF}]/u', $text, "Arabic in the English file: $key");
            }
        }
    }

    public function test_no_arabic_is_written_directly_in_the_landing_views(): void
    {
        foreach (['index', 'includes/header', 'includes/footer'] as $view) {
            $blade = file_get_contents(resource_path("views/frontend/$view.blade.php"));
            $blade = preg_replace(['/\{\{--.*?--\}\}/s', '/<!--.*?-->/s'], '', $blade);   // comments don't count

            $this->assertDoesNotMatchRegularExpression('/[\x{0600}-\x{06FF}]/u', $blade, "$view still has hard-coded Arabic");
        }
    }
    /**
     * Run a test with a throw-away package on the landing page, then roll the database back.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function withPackage(array $attributes, callable $test): void
    {
        DB::beginTransaction();

        try {
            $package = SubscriptionPackage::create([...[
                'title' => 'الباقة التجريبية', 'period' => 1, 'price' => 0.5, 'currency' => 'OMR', 'is_active' => true, 'master_id' => 1,
            ], ...$attributes]);

            $test($package);
        } finally {
            DB::rollBack();
        }
    }

    public function test_package_texts_follow_the_language_when_english_versions_exist(): void
    {
        $this->withPackage([
            'title' => 'باقة اختبار الترجمة', 'title_en' => 'Translation Test Plan',
            'description' => 'وصف عربي للباقة', 'description_en' => 'An English description',
            'savings_note' => 'ملاحظة توفير عربية', 'savings_note_en' => 'English savings note',
            'cta_label' => 'زر اختبار الترجمة', 'cta_label_en' => 'Translation test button',
            'traditional_cost_items' => [['label' => 'بند اختبار الإيجار', 'label_en' => 'Test rent item', 'value' => 100]],
            'feature_bullets' => [['text' => 'ميزة اختبار الترجمة', 'text_en' => 'Translation test feature']],
        ], function () {
            $this->get('/lang/ar');
            $this->get('/')->assertOk()
                ->assertSee('باقة اختبار الترجمة')->assertSee('وصف عربي للباقة')->assertSee('ملاحظة توفير عربية')
                ->assertSee('زر اختبار الترجمة')->assertSee('بند اختبار الإيجار')->assertSee('ميزة اختبار الترجمة')
                ->assertDontSee('Translation Test Plan');

            $this->get('/lang/en');
            $this->get('/')->assertOk()
                ->assertSee('Translation Test Plan')->assertSee('An English description')->assertSee('English savings note')
                ->assertSee('Translation test button')->assertSee('Test rent item')->assertSee('Translation test feature')
                ->assertDontSee('باقة اختبار الترجمة')->assertDontSee('وصف عربي للباقة')->assertDontSee('ملاحظة توفير عربية')
                ->assertDontSee('زر اختبار الترجمة')->assertDontSee('بند اختبار الإيجار')->assertDontSee('ميزة اختبار الترجمة');
        });
    }

    public function test_when_an_english_version_is_missing_the_button_and_note_never_show_arabic(): void
    {
        $this->withPackage([
            'title' => 'باقة بلا ترجمة', 'description' => 'وصف بلا ترجمة',
            'savings_note' => 'ملاحظة بلا ترجمة', 'cta_label' => 'زر بلا ترجمة',
            'traditional_cost_items' => [['label' => 'بند بلا ترجمة', 'value' => 100]],
        ], function () {
            $this->get('/lang/en');

            $this->get('/')->assertOk()
                ->assertSee('باقة بلا ترجمة')             // the title has nothing better to fall back on
                ->assertSee('وصف بلا ترجمة')
                ->assertSee('بند بلا ترجمة')               // so does an untranslated cost item
                ->assertDontSee('زر بلا ترجمة')               // the button uses the page's own English wording
                ->assertDontSee('ملاحظة بلا ترجمة')        // and the savings note is simply left out
                ->assertSee('Purchase Now');
        });
    }

    public function test_the_package_text_helpers_pick_the_right_language(): void
    {
        $package = new SubscriptionPackage(['title' => 'عربي', 'title_en' => 'English', 'cta_label' => 'زر', 'cta_label_en' => '']);

        app()->setLocale('ar');
        $this->assertSame('عربي', $package->localized('title'));
        $this->assertSame('زر', $package->localized('cta_label'));
        $this->assertSame('x-ar', SubscriptionPackage::localizedItem(['label' => 'x-ar', 'label_en' => 'x-en'], 'label'));

        app()->setLocale('en');
        $this->assertSame('English', $package->localized('title'));
        $this->assertNull($package->localized('cta_label'));                  // empty English -> default wording
        $this->assertSame('x-en', SubscriptionPackage::localizedItem(['label' => 'x-ar', 'label_en' => 'x-en'], 'label'));
        $this->assertSame('x-ar', SubscriptionPackage::localizedItem(['label' => 'x-ar'], 'label'));
        $this->assertSame('x-ar', SubscriptionPackage::localizedItem(['label' => 'x-ar', 'label_en' => ''], 'label'));

        app()->setLocale('ar');
    }
}
