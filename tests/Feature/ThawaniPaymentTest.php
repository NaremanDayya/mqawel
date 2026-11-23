<?php

namespace Tests\Feature;

use App\Http\Controllers\Frontend\ThawaniController;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use App\Models\User;
use App\Services\Thawani;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Paying for a subscription through Thawani. Runs against the real dev database inside a
 * rolled-back transaction, like the rest of this suite; Thawani itself is always faked.
 */
class ThawaniPaymentTest extends TestCase
{
    private const SECRET = 'test-secret-key';

    private const PUBLISHABLE = 'test-publishable-key';

    private Company $company;

    private User $user;

    private SubscriptionPackage $package;

    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();

        config([
            'services.thawani.mode' => 'test',
            'services.thawani.secret_key' => self::SECRET,
            'services.thawani.publishable_key' => self::PUBLISHABLE,
        ]);

        $this->company = Company::create(['name' => 'Thawani Test Co '.uniqid()]);
        $this->user = User::factory()->create(['company_id' => $this->company->id]);
        $this->package = SubscriptionPackage::create([
            'title' => 'Gold', 'period' => 12, 'price' => 120, 'currency' => 'OMR', 'is_active' => true, 'master_id' => 1,
        ]);
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function fakeSession(string $id = 'sess_123'): void
    {
        Http::fake(['*checkout/session' => Http::response(['success' => true, 'data' => ['session_id' => $id]])]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function pending(array $overrides = []): array
    {
        return [...[
            'session_id' => 'sess_123',
            'company_id' => $this->company->id,
            'package_id' => $this->package->id,
            'months' => 3,
            'amount' => 30.0,
            'baisa' => 30000,
        ], ...$overrides];
    }

    private function paid(array $overrides = []): void
    {
        Http::fake(['*checkout/session/*' => Http::response(['success' => true, 'data' => [...[
            'payment_status' => 'paid', 'total_amount' => 30000,
        ], ...$overrides]])]);
    }

    private function subscriptionsForTransaction(string $id = 'sess_123'): int
    {
        return Subscription::where('payment_transaction_id', $id)->count();
    }

    // ---- starting a payment

    public function test_the_keys_and_gateway_come_from_the_environment_and_the_customer_is_sent_to_pay(): void
    {
        $this->fakeSession();

        $response = $this->actingAs($this->user)->get(route('payment', ['package_id' => $this->package->id, 'months' => 3]));

        $response->assertRedirect('https://uatcheckout.thawani.om/pay/sess_123?key='.self::PUBLISHABLE);

        Http::assertSent(fn (HttpRequest $request) => $request->url() === 'https://uatcheckout.thawani.om/api/v1/checkout/session'
            && $request->header('thawani-api-key') === [self::SECRET]
            && $request['products'][0]['unit_amount'] === 30000);

        $pending = session('thawani_payment');
        $this->assertSame(['session_id' => 'sess_123', 'company_id' => $this->company->id, 'package_id' => $this->package->id, 'months' => 3, 'amount' => 30.0, 'baisa' => 30000], $pending);
    }

    public function test_live_mode_uses_the_live_gateway_and_only_the_exact_word_live_counts(): void
    {
        $thawani = new Thawani;

        config(['services.thawani.mode' => 'live']);
        $this->assertSame('https://checkout.thawani.om', $thawani->baseUrl());

        foreach (['Live', 'LIVE', 'production', '', null] as $notLive) {
            config(['services.thawani.mode' => $notLive]);
            $this->assertSame('https://uatcheckout.thawani.om', $thawani->baseUrl(), 'mode '.var_export($notLive, true));
        }
    }

    public function test_without_keys_nothing_is_sent_and_the_customer_sees_the_failed_page(): void
    {
        config(['services.thawani.secret_key' => null]);
        Http::fake();

        $this->actingAs($this->user)->get(route('payment', ['package_id' => $this->package->id]))
            ->assertRedirect(route('subscribe', ['failed_payment' => true]));

        Http::assertNothingSent();
        $this->assertNull(session('thawani_payment'));
    }

    public function test_a_guest_is_sent_to_sign_up_and_thawani_is_not_called(): void
    {
        Http::fake();

        $this->get(route('payment', ['package_id' => $this->package->id]))->assertRedirect(route('sign-up'));

        Http::assertNothingSent();
    }

    public function test_when_thawani_is_unreachable_or_refuses_the_customer_sees_the_failed_page(): void
    {
        Http::fake(fn () => throw new ConnectionException('timeout'));
        $this->actingAs($this->user)->get(route('payment', ['package_id' => $this->package->id]))
            ->assertRedirect(route('subscribe', ['failed_payment' => true]));

        Http::fake(['*' => Http::response(['success' => false], 401)]);
        $this->get(route('payment', ['package_id' => $this->package->id]))
            ->assertRedirect(route('subscribe', ['failed_payment' => true]));

        $this->assertNull(session('thawani_payment'));
    }

    // ---- coming back from Thawani

    public function test_a_paid_session_creates_the_subscription_once_with_the_transaction_id(): void
    {
        $this->paid();

        $this->actingAs($this->user)->withSession(['thawani_payment' => $this->pending()])
            ->get(route('payment-success'))->assertRedirect(url('company'));

        $subscription = Subscription::where('payment_transaction_id', 'sess_123')->firstOrFail();

        $this->assertSame($this->company->id, $subscription->company_id);
        $this->assertSame($this->package->id, $subscription->package_id);
        $this->assertSame(3, $subscription->period);
        $this->assertSame('30.00', $subscription->price);
        $this->assertSame('payment-gateway', $subscription->payment_method);
        $this->assertTrue((bool) $subscription->is_active);
        $this->assertNull(session('thawani_payment'));
    }

    public function test_reloading_the_success_page_never_gives_a_second_subscription_for_one_payment(): void
    {
        $this->paid();

        $this->actingAs($this->user)->withSession(['thawani_payment' => $this->pending()])->get(route('payment-success'));
        // The same payment replayed (e.g. the session data is still there on a second request).
        $this->withSession(['thawani_payment' => $this->pending()])->get(route('payment-success'))->assertRedirect(url('company'));

        $this->assertSame(1, $this->subscriptionsForTransaction());
    }

    public function test_opening_the_success_page_without_paying_does_not_create_a_subscription(): void
    {
        // The customer started a payment but never paid: Thawani says unpaid.
        $this->paid(['payment_status' => 'unpaid']);

        $this->actingAs($this->user)->withSession(['thawani_payment' => $this->pending()])
            ->get(route('payment-success'))->assertRedirect(route('subscribe', ['failed_payment' => true]));

        $this->assertSame(0, $this->subscriptionsForTransaction());

        foreach (['cancelled', 'expired', null] as $status) {
            $this->paid(['payment_status' => $status]);
            $this->get(route('payment-success'))->assertRedirect(route('subscribe', ['failed_payment' => true]));
        }

        $this->assertSame(0, Subscription::where('company_id', $this->company->id)->count());
    }

    public function test_visiting_the_success_page_directly_does_nothing(): void
    {
        Http::fake();

        $this->actingAs($this->user)->get(route('payment-success'))->assertRedirect(route('subscribe', ['failed_payment' => true]));

        Http::assertNothingSent();
        $this->assertSame(0, Subscription::where('company_id', $this->company->id)->count());
    }

    public function test_a_payment_started_by_another_company_cannot_be_claimed(): void
    {
        $this->paid();
        $other = Company::create(['name' => 'Other Co '.uniqid()]);

        $this->actingAs($this->user)->withSession(['thawani_payment' => $this->pending(['company_id' => $other->id])])
            ->get(route('payment-success'))->assertRedirect(route('subscribe', ['failed_payment' => true]));

        $this->assertSame(0, $this->subscriptionsForTransaction());
    }

    public function test_a_paid_amount_that_differs_from_what_was_asked_is_refused(): void
    {
        $this->paid(['total_amount' => 1000]);

        $this->actingAs($this->user)->withSession(['thawani_payment' => $this->pending()])
            ->get(route('payment-success'))->assertRedirect(route('subscribe', ['failed_payment' => true]));

        $this->assertSame(0, $this->subscriptionsForTransaction());
    }

    public function test_when_thawani_cannot_be_asked_no_subscription_is_given(): void
    {
        Http::fake(fn () => throw new ConnectionException('timeout'));

        $this->actingAs($this->user)->withSession(['thawani_payment' => $this->pending()])
            ->get(route('payment-success'))->assertRedirect(route('subscribe', ['failed_payment' => true]));

        $this->assertSame(0, $this->subscriptionsForTransaction());
    }

    public function test_a_cancelled_payment_forgets_the_pending_payment(): void
    {
        Http::fake();

        $this->actingAs($this->user)->withSession(['thawani_payment' => $this->pending()])
            ->get(route('payment-fail'))->assertRedirect(route('subscribe', ['failed_payment' => true]));

        $this->assertNull(session('thawani_payment'));
    }

    public function test_the_leftover_refund_action_that_dumped_a_response_is_gone(): void
    {
        $this->assertFalse(method_exists(ThawaniController::class, 'refund'));
    }

    public function test_no_gateway_key_is_written_in_the_code(): void
    {
        $code = file_get_contents(app_path('Http/Controllers/Frontend/ThawaniController.php')).file_get_contents(app_path('Services/Thawani.php'));

        $this->assertStringNotContainsString('thawani-api-key: ', $code);
        $this->assertDoesNotMatchRegularExpression('/[A-Za-z0-9]{30}/', $code);
    }
}
