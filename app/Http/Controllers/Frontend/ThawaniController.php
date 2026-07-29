<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Ixudra\Curl\Facades\Curl;

class ThawaniController extends Controller
{
    public function payment(Request $request){
        $Package = SubscriptionPackage::where('is_active', true)->findOrFail($request->package_id);

        $months = max(1, (int) $request->input('months', 1));
        $monthlyPrice = $Package->price / $Package->period;
        $amount = round($monthlyPrice * $months, 3);

        $unit_amount_in_baisa = (int)($amount * 1000);

        $data['client_reference_id']= time();
        $data['mode']= 'payment';
        $data['products'][0]['name']= $Package->title.' - '.$months.' '.__('frontend.months');
        $data['products'][0]['quantity']= 1;
        $data['products'][0]['unit_amount']= $unit_amount_in_baisa;
        $data['success_url']= route('payment-success');
        $data['cancel_url']= route('payment-fail');
        $data['metadata']= [
            'customer_name'  => 'Mqwel',
            'customer_email' => 'no-reply@mqwel.com',
            'order_id'       => (string)time(),
        ];

        $response = Curl::to('https://uatcheckout.thawani.om/api/v1/checkout/session')
            ->withHeader('Content-Type: application/json')
            ->withHeader('thawani-api-key: rRQ26GcsZzoEhbrP2HZvLYDbn9C9et')
            ->withData($data)
            ->asJson()
            ->post();

        \Session::put('pay_session_id', $response->data->session_id);
        \Session::put('package_id', $Package->id);
        \Session::put('subscription_months', $months);
        \Session::put('subscription_amount', $amount);

        $to = 'https://uatcheckout.thawani.om/pay/'.$response->data->session_id.'?key=HGvTMLDssJghr9tlN9gr4DVYt0qyBy';

        return redirect()->to($to);
    }

    public function success(){
        $sessionId = \Session::get('pay_session_id');
        $package_id= \Session::get('package_id');
        $months= \Session::get('subscription_months');
        $amount= \Session::get('subscription_amount');

        $urls = 'https://uatcheckout.thawani.om/api/v1/checkout/session/'.$sessionId;

        $response = Curl::to($urls)
            ->withHeader('Content-Type: application/json')
            ->withHeader('thawani-api-key: rRQ26GcsZzoEhbrP2HZvLYDbn9C9et')
            ->asJson()
            ->get();

        //dd($response);

        $Package= SubscriptionPackage::find($package_id);

        //If package is null then refund.

        $months= $months ?: $Package->period;
        $amount= $amount ?: $Package->price;

        $Subscription= new Subscription();
        $Subscription->company_id= Auth::user()->company_id;
        $Subscription->package_id= $Package->id;
        $Subscription->period= $months;
        $Subscription->starting_date= date('Y-m-d');
        $Subscription->ending_date= date('Y-m-d', strtotime('+ '.$months.' month', strtotime(date('Y-m-d'))));
        $Subscription->price= $amount;
        $Subscription->currency= $Package->currency;
        $Subscription->payment_method= 'payment-gateway';
        $Subscription->payment_transaction_id= null;
        $Subscription->payment_date= date('Y-m-d');
        $Subscription->is_active= true;
        $Subscription->master_id= null;
        $Subscription->save();

        return redirect()->to(url('company'));
    }

    public function fail(){
        $sessionId = \Session::get('pay_session_id');

        $urls = 'https://uatcheckout.thawani.om/api/v1/checkout/session/'.$sessionId;

        $response = Curl::to($urls)
            ->withHeader('Content-Type: application/json')
            ->withHeader('thawani-api-key: rRQ26GcsZzoEhbrP2HZvLYDbn9C9et')
            ->asJson()
            ->get();

        //dd($response);

        return redirect()->route('subscribe', ['failed_payment' => true]);
    }

    public function refund(){
        $sessionId = \Session::get('pay_session_id');

        $urls = 'https://uatcheckout.thawani.om/api/v1/checkout/session/'.$sessionId;

        $response = Curl::to($urls)
            ->withHeader('Content-Type: application/json')
            ->withHeader('thawani-api-key: rRQ26GcsZzoEhbrP2HZvLYDbn9C9et')
            ->asJson()
            ->get();

        $paymentObject = Curl::to('https://uatcheckout.thawani.om/api/v1/payments?checkout_invoice='.$response->data->invoice)
            ->withHeader('Content-Type: application/json')
            ->withHeader('thawani-api-key: rRQ26GcsZzoEhbrP2HZvLYDbn9C9et')
            ->asJson()
            ->get();

        $card = current($paymentObject->data);

        $refund = Curl::to('https://uatcheckout.thawani.om/api/v1/refunds')
            ->withHeader('Content-Type: application/json')
            ->withHeader('thawani-api-key: rRQ26GcsZzoEhbrP2HZvLYDbn9C9et')
            ->withData(['payment_id' => $card->payment_id,'reason' => 'refund'])
            ->asJson()
            ->post();
                
        $refundStatus = Curl::to('https://uatcheckout.thawani.om/api/v1/refunds/'.$refund->data->refund_id)
            ->withHeader('Content-Type: application/json')
            ->withHeader('thawani-api-key: rRQ26GcsZzoEhbrP2HZvLYDbn9C9et')
            ->asJson()
            ->get();

        dd($refundStatus);
    }
}
