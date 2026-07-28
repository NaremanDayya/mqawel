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
        $package_id= $request->package_id;

        $unit_amount_in_baisa = (int)($request->payment_amount * 1000);

        $data['client_reference_id']= time();
        $data['mode']= 'payment';
        $data['products'][0]['name']= $request->payment_name;
        $data['products'][0]['quantity']= $request->payment_quantity;
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
        \Session::put('package_id', $package_id);

        $to = 'https://uatcheckout.thawani.om/pay/'.$response->data->session_id.'?key=HGvTMLDssJghr9tlN9gr4DVYt0qyBy';
       
        return redirect()->to($to);
    }

    public function success(){
        $sessionId = \Session::get('pay_session_id');
        $package_id= \Session::get('package_id');

        $urls = 'https://uatcheckout.thawani.om/api/v1/checkout/session/'.$sessionId;

        $response = Curl::to($urls)
            ->withHeader('Content-Type: application/json')
            ->withHeader('thawani-api-key: rRQ26GcsZzoEhbrP2HZvLYDbn9C9et')
            ->asJson()
            ->get();

        //dd($response);

        $Package= SubscriptionPackage::find($package_id);

        //If package is null then refund.

        $Subscription= new Subscription();
        $Subscription->company_id= Auth::user()->company_id;
        $Subscription->package_id= $Package->id;
        $Subscription->period= $Package->period;
        $Subscription->starting_date= date('Y-m-d');
        $Subscription->ending_date= date('Y-m-d', strtotime('+ '.$Package->period.' month', strtotime(date('Y-m-d'))));
        $Subscription->price= $Package->price;
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
