<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use App\Services\Thawani;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Pays for a subscription package through Thawani. The keys and the test/live switch live in
 * .env (THAWANI_*), and a subscription is only created after Thawani itself reports the payment
 * as paid — never just because the customer's browser came back to the success page.
 */
class ThawaniController extends Controller
{
    private const SESSION_KEY = 'thawani_payment';

    public function __construct(private Thawani $thawani) {}

    public function payment(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('sign-up');
        }

        if (! $this->thawani->isConfigured()) {
            report(new \RuntimeException('Thawani is not configured: set THAWANI_SECRET_KEY and THAWANI_PUBLISHABLE_KEY.'));

            return $this->failed();
        }

        $Package = SubscriptionPackage::where('is_active', true)->findOrFail($request->package_id);

        $months = max(1, (int) $request->input('months', 1));
        $amount = round($Package->priceForMonths($months), 3);

        $unit_amount_in_baisa = (int) round($amount * 1000);

        $data['client_reference_id'] = time();
        $data['mode'] = 'payment';
        $data['products'][0]['name'] = $Package->title.' - '.$months.' '.__('frontend.months');
        $data['products'][0]['quantity'] = 1;
        $data['products'][0]['unit_amount'] = $unit_amount_in_baisa;
        $data['success_url'] = route('payment-success');
        $data['cancel_url'] = route('payment-fail');
        $data['metadata'] = [
            'customer_name' => 'Mqwel',
            'customer_email' => 'no-reply@mqwel.com',
            'order_id' => (string) time(),
        ];

        $sessionId = $this->thawani->createSession($data);

        if (! $sessionId) {
            return $this->failed();
        }

        // What we asked for, kept on the server: the success page checks Thawani against this.
        session()->put(self::SESSION_KEY, [
            'session_id' => $sessionId,
            'company_id' => Auth::user()->company_id,
            'package_id' => $Package->id,
            'months' => $months,
            'amount' => $amount,
            'baisa' => $unit_amount_in_baisa,
        ]);

        return redirect()->to($this->thawani->paymentUrl($sessionId));
    }

    public function success(): RedirectResponse
    {
        $user = Auth::user();
        $pending = session()->get(self::SESSION_KEY);

        if (! $user || ! $pending || $pending['company_id'] !== $user->company_id) {
            return $this->failed();
        }

        // Only Thawani's own word that it was paid counts.
        if (! $this->thawani->isPaid($this->thawani->session($pending['session_id']), $pending['baisa'])) {
            return $this->failed();
        }

        $Package = SubscriptionPackage::find($pending['package_id']);

        if (! $Package) {
            report(new \RuntimeException('Thawani payment '.$pending['session_id'].' was paid for a package that no longer exists.'));

            return $this->failed();
        }

        // Reloading the success page must not hand out a second subscription for one payment.
        DB::transaction(function () use ($user, $Package, $pending) {
            if (Subscription::where('payment_transaction_id', $pending['session_id'])->exists()) {
                return;
            }

            $Subscription = new Subscription();
            $Subscription->company_id = $user->company_id;
            $Subscription->package_id = $Package->id;
            $Subscription->period = $pending['months'];
            $Subscription->starting_date = date('Y-m-d');
            $Subscription->ending_date = date('Y-m-d', strtotime('+ '.$pending['months'].' month', strtotime(date('Y-m-d'))));
            $Subscription->price = $pending['amount'];
            $Subscription->currency = $Package->currency;
            $Subscription->payment_method = 'payment-gateway';
            $Subscription->payment_transaction_id = $pending['session_id'];
            $Subscription->payment_date = date('Y-m-d');
            $Subscription->is_active = true;
            $Subscription->master_id = null;
            $Subscription->save();
        });

        session()->forget(self::SESSION_KEY);

        return redirect()->to(url('company'));
    }

    public function fail(): RedirectResponse
    {
        session()->forget(self::SESSION_KEY);

        return $this->failed();
    }

    private function failed(): RedirectResponse
    {
        return redirect()->route('subscribe', ['failed_payment' => true]);
    }
}
