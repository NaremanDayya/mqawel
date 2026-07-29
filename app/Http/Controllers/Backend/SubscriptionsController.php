<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPackage;
use Illuminate\Http\Request;

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

        return view('backend.subscribe-checkout', compact('Package'));
    }
}
