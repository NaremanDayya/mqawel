<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The free trial package: how it is stored, how a company starts it on the website, and how the
 * admin-created subscriptions use its length. Runs against the dev database inside a rolled-back
 * transaction, like the rest of this suite.
 */
class FreeTrialPackageTest extends TestCase
{
    private Company $company;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();

        $this->company = Company::create(['name' => 'Trial Test Co '.uniqid()]);
        $this->user = User::factory()->create(['company_id' => $this->company->id]);
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function trial(array $overrides = []): SubscriptionPackage
    {
        return SubscriptionPackage::create([...[
            'title' => 'Free Trial '.uniqid(), 'is_trial' => true, 'trial_days' => 14, 'currency' => 'OMR', 'is_active' => true, 'master_id' => 1,
        ], ...$overrides]);
    }

    private function paid(array $overrides = []): SubscriptionPackage
    {
        return SubscriptionPackage::create([...[
            'title' => 'Paid '.uniqid(), 'period' => 12, 'price' => 120, 'currency' => 'OMR', 'is_active' => true, 'master_id' => 1,
        ], ...$overrides]);
    }

    // ---- the package itself

    public function test_a_trial_package_is_always_free_and_keeps_a_valid_length(): void
    {
        $package = SubscriptionPackage::create([
            'title' => 'T', 'is_trial' => true, 'price' => 99, 'period' => 12, 'is_active' => true, 'master_id' => 1,
        ]);

        $this->assertSame('0.00', $package->fresh()->price);
        $this->assertSame(14, $package->fresh()->trial_days);   // a missing length falls back to 14 days
        $this->assertSame(1, $package->fresh()->period);        // the month column stays valid

        $long = $this->trial(['trial_days' => 90]);
        $this->assertSame(3, $long->fresh()->period);
    }

    public function test_a_normal_package_is_left_alone(): void
    {
        $package = $this->paid();

        $this->assertFalse($package->fresh()->is_trial);
        $this->assertSame('120.00', $package->fresh()->price);
        $this->assertSame(12, $package->fresh()->period);
    }

    public function test_the_end_date_counts_days_for_a_trial_and_months_for_a_paid_package(): void
    {
        $this->assertSame('2026-12-14', $this->trial()->endingDateFrom('2026-12-01'));          // 14 days, last day counts
        $this->assertSame('2026-12-01', $this->trial(['trial_days' => 1])->endingDateFrom('2026-12-01'));
        $this->assertSame('2027-12-01', $this->paid()->endingDateFrom('2026-12-01'));
    }

    // ---- starting the trial on the website

    public function test_a_company_with_no_subscription_starts_the_trial_without_paying(): void
    {
        $package = $this->trial();

        $this->actingAs($this->user)->post(route('subscribe.trial'), ['package_id' => $package->id])
            ->assertRedirect(url('company'));

        $subscription = Subscription::where('company_id', $this->company->id)->firstOrFail();

        $this->assertSame($package->id, $subscription->package_id);
        $this->assertSame(date('Y-m-d'), $subscription->starting_date);
        $this->assertSame(date('Y-m-d', strtotime('+13 day')), $subscription->ending_date);
        $this->assertSame('0.00', $subscription->price);
        $this->assertSame('free-trial', $subscription->payment_method);
        $this->assertTrue((bool) $subscription->is_active);
    }

    public function test_the_trial_is_given_once_per_company(): void
    {
        $package = $this->trial();

        $this->actingAs($this->user)->post(route('subscribe.trial'), ['package_id' => $package->id])->assertRedirect(url('company'));
        $this->post(route('subscribe.trial'), ['package_id' => $package->id])
            ->assertRedirect(route('subscribe'))->assertSessionHas('trial_unavailable');

        $this->assertSame(1, Subscription::where('company_id', $this->company->id)->count());
    }

    public function test_a_company_that_already_subscribed_cannot_take_the_trial(): void
    {
        $paid = $this->paid();
        Subscription::create([
            'company_id' => $this->company->id, 'package_id' => $paid->id, 'period' => 12, 'starting_date' => '2025-01-01',
            'ending_date' => '2025-12-31', 'price' => 120, 'is_active' => true,
        ]);

        $this->actingAs($this->user)->post(route('subscribe.trial'), ['package_id' => $this->trial()->id])
            ->assertRedirect(route('subscribe'))->assertSessionHas('trial_unavailable');

        $this->assertSame(1, Subscription::where('company_id', $this->company->id)->count());
    }

    public function test_a_paid_or_switched_off_package_can_never_be_had_through_the_trial_route(): void
    {
        $this->actingAs($this->user);

        $this->post(route('subscribe.trial'), ['package_id' => $this->paid()->id])->assertNotFound();
        $this->post(route('subscribe.trial'), ['package_id' => $this->trial(['is_active' => false])->id])->assertNotFound();

        $this->assertSame(0, Subscription::where('company_id', $this->company->id)->count());
    }

    public function test_a_guest_is_sent_to_sign_up(): void
    {
        $this->post(route('subscribe.trial'), ['package_id' => $this->trial()->id])->assertRedirect(route('sign-up'));

        $this->assertSame(0, Subscription::where('company_id', $this->company->id)->count());
    }

    // ---- what the website shows

    public function test_the_packages_page_shows_the_trial_as_free_for_its_days_next_to_the_paid_ones(): void
    {
        $this->trial(['title' => 'My Free Trial', 'trial_days' => 21]);
        $this->paid(['title' => 'My Gold Plan']);

        // (the packages page is for signed-in users: its header shows their name and company)
        $this->actingAs($this->user)->get(route('subscribe'))->assertOk()
            ->assertSee('My Free Trial')
            ->assertSee('مجاناً')
            ->assertSee('لمدة 21 يوماً')
            ->assertSee('My Gold Plan');
    }

    public function test_the_checkout_page_offers_the_trial_button_only_to_a_company_that_can_take_it(): void
    {
        $package = $this->trial();

        $this->actingAs($this->user)->get(route('subscribe.checkout', ['package_id' => $package->id]))
            ->assertOk()
            ->assertSee('ابدأ التجربة المجانية')
            ->assertSee(route('subscribe.trial'), false)
            ->assertDontSee(route('payment'), false);

        $this->post(route('subscribe.trial'), ['package_id' => $package->id]);

        $this->get(route('subscribe.checkout', ['package_id' => $package->id]))
            ->assertOk()
            ->assertDontSee('ابدأ التجربة المجانية')
            ->assertSee('مرة واحدة لكل شركة');
    }

    public function test_a_paid_package_checkout_is_unchanged(): void
    {
        $package = $this->paid();

        $this->actingAs($this->user)->get(route('subscribe.checkout', ['package_id' => $package->id]))
            ->assertOk()
            ->assertSee(route('payment'), false)
            ->assertDontSee(route('subscribe.trial'), false);
    }

    // ---- the seeder

    public function test_the_seeder_creates_one_trial_package_with_every_feature_and_never_overwrites_it(): void
    {
        SubscriptionPackage::where('is_trial', true)->delete();

        if (! \App\Models\Master::exists()) {
            $this->markTestSkipped('needs an admin (master) in the dev database');
        }

        $this->seed(\Database\Seeders\FreeTrialPackageSeeder::class);

        $package = SubscriptionPackage::where('is_trial', true)->firstOrFail();
        $this->assertSame(14, $package->trial_days);
        $this->assertTrue((bool) $package->has_workers && (bool) $package->has_project_expenses_report);

        $package->update(['title' => 'Edited', 'is_active' => false]);
        $this->seed(\Database\Seeders\FreeTrialPackageSeeder::class);

        $this->assertSame(1, SubscriptionPackage::where('is_trial', true)->count());
        $this->assertSame('Edited', $package->fresh()->title);
    }
}
