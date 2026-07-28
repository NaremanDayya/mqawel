<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
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
}
