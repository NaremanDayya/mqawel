<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\SubscriptionPackage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The yearly (12-month) price: a value the admin sets per package, defaulting to 10 months'
 * worth, shown on the pricing page and used at checkout. Runs inside a rolled-back transaction.
 */
class YearlyPriceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function package(array $overrides = []): SubscriptionPackage
    {
        return SubscriptionPackage::create([...[
            'title' => 'Plan '.uniqid(), 'period' => 1, 'price' => 10, 'currency' => 'OMR', 'is_active' => true, 'master_id' => 1,
        ], ...$overrides]);
    }

    public function test_without_a_yearly_price_a_year_costs_ten_months(): void
    {
        $package = $this->package(['price' => 10, 'period' => 1]);

        $this->assertSame(10.0, $package->monthlyRate());
        $this->assertSame(100.0, $package->yearlyPrice());

        // A package priced for several months works from its monthly rate.
        $this->assertSame(100.0, $this->package(['price' => 30, 'period' => 3])->yearlyPrice());
    }

    public function test_the_admins_yearly_price_wins(): void
    {
        $package = $this->package(['price' => 10, 'yearly_price' => 85]);

        $this->assertSame(85.0, $package->fresh()->yearlyPrice());
    }

    public function test_twelve_months_use_the_yearly_price_and_other_lengths_the_monthly_rate(): void
    {
        $package = $this->package(['price' => 10, 'yearly_price' => 85]);

        $this->assertSame(85.0, $package->priceForMonths(12));
        $this->assertSame(10.0, $package->priceForMonths(1));
        $this->assertSame(30.0, $package->priceForMonths(3));
        $this->assertSame(60.0, $package->priceForMonths(6));
    }

    public function test_a_package_that_offers_a_trial_keeps_its_yearly_price(): void
    {
        $trial = $this->package(['is_trial' => true, 'trial_days' => 14, 'yearly_price' => 50]);

        $this->assertSame('50.00', $trial->fresh()->yearly_price);
        $this->assertSame(50.0, $trial->fresh()->yearlyPrice());
    }

    public function test_the_pricing_page_shows_the_yearly_value_and_twelve_months_and_the_free_days_with_the_price_after(): void
    {
        $this->package(['title' => 'Yearly Gold', 'price' => 10, 'yearly_price' => 85]);
        $this->package(['title' => 'Default Silver', 'price' => 5]);
        $this->package(['title' => 'Trial Plan', 'is_trial' => true, 'trial_days' => 14, 'price' => 20, 'yearly_price' => 180]);

        $html = $this->get('/')->assertOk()->assertSee('Yearly Gold')->assertSee('Trial Plan')->getContent();

        $this->assertStringContainsString('data-monthly="10" data-yearly="85"', $html);   // the admin's yearly value
        $this->assertStringContainsString('data-monthly="5" data-yearly="50"', $html);    // 10 months of 5, by default
        $this->assertStringContainsString('data-yearly="/ 12 ', $html);                    // per 12 months, not per 1
        $this->assertStringNotContainsString('data-yearly="/ 1 ', $html);

        // A package that offers a trial keeps its monthly AND yearly price, and says what applies after the free days.
        $this->assertStringContainsString('جرّب مجاناً 14 يوماً', $html);
        $this->assertStringContainsString('data-monthly="20" data-yearly="180"', $html);
        $this->assertStringContainsString('data-monthly="ثم 20 ', $html);   // "then 20 OMR / month"
        $this->assertStringContainsString('data-yearly="ثم 180 ', $html);   // "then 180 OMR / 12 months" when yearly is chosen
    }

    public function test_checkout_charges_the_yearly_price_for_twelve_months_and_the_monthly_rate_otherwise(): void
    {
        config([
            'services.thawani.mode' => 'test',
            'services.thawani.secret_key' => 'k', 'services.thawani.publishable_key' => 'p',
        ]);
        $company = Company::create(['name' => 'Yearly Co '.uniqid()]);
        $user = User::factory()->create(['company_id' => $company->id]);
        $package = $this->package(['price' => 10, 'yearly_price' => 85]);
        Http::fake(['*checkout/session' => Http::response(['data' => ['session_id' => 's1']])]);

        $this->actingAs($user)->get(route('payment', ['package_id' => $package->id, 'months' => 12]));
        $this->assertSame(85000, session('thawani_payment')['baisa']);

        $this->get(route('payment', ['package_id' => $package->id, 'months' => 3]));
        $this->assertSame(30000, session('thawani_payment')['baisa']);

        $this->get(route('subscribe.checkout', ['package_id' => $package->id]))
            ->assertOk()->assertSee('data-yearly-price="85"', false);
    }
}
