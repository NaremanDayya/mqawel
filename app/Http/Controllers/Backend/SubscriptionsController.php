<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubscriptionsController extends Controller
{
    public function subscribe(Request $request){
        $failed_payment= false;

        if($request->exists('failed_payment') && !empty($request->failed_payment)){
            $failed_payment= true;
        }

        return view('backend.subscribe', compact('failed_payment'));
    }

    public function checkout(Request $request){
        $Package = SubscriptionPackage::where('is_active', true)->findOrFail($request->package_id);

        // The free trial is for a company that has never subscribed to anything.
        $canTrial = $Package->is_trial && Auth::check() && Auth::user()->company_id
            && ! Subscription::where('company_id', Auth::user()->company_id)->exists();

        return view('backend.subscribe-checkout', compact('Package', 'canTrial'));
    }

    /**
     * Start the free trial: no payment, once per company, and only for an active trial package —
     * a paid package posted here is refused (404), so it can never be had for free.
     */
    public function startTrial(Request $request): RedirectResponse
    {
        if (! Auth::check() || ! Auth::user()->company_id) {
            return redirect()->route('sign-up');
        }

        $Package = SubscriptionPackage::where(['is_active' => true, 'is_trial' => true])->findOrFail($request->package_id);
        $companyId = Auth::user()->company_id;

        $started = DB::transaction(function () use ($Package, $companyId) {
            if (Subscription::where('company_id', $companyId)->lockForUpdate()->exists()) {
                return false;
            }

            $Subscription = new Subscription();
            $Subscription->company_id = $companyId;
            $Subscription->package_id = $Package->id;
            $Subscription->period = $Package->period;
            $Subscription->starting_date = date('Y-m-d');
            $Subscription->ending_date = $Package->trialEndingDate(date('Y-m-d'));
            $Subscription->price = 0;
            $Subscription->currency = $Package->currency;
            $Subscription->payment_method = 'free-trial';
            $Subscription->payment_transaction_id = null;
            $Subscription->payment_date = date('Y-m-d');
            $Subscription->is_active = true;
            $Subscription->master_id = null;
            $Subscription->save();

            return true;
        });

        if (! $started) {
            return redirect()->route('subscribe')->with('trial_unavailable', true);
        }

        return redirect()->to(url('company'));
    }
}
