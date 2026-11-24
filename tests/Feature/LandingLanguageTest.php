<?php

namespace Tests\Feature;

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
}
