<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAccessPanelMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if(Auth::check()){
            if(!Auth::user()->is_active){
                return redirect()->route('index');
            }

            if(!Auth::user()->company || !Auth::user()->company->is_active){
                return redirect()->route('index');
            }

            $Subscription= Subscription::where(['company_id' => Auth::user()->company_id, 'is_active' => 1])->orderBy('id', 'desc')->first();

            if(!$Subscription){
                return redirect()->route('subscribe');
            }

            if(empty($Subscription->ending_date) || $Subscription->ending_date < date('Y-m-d')){
                return redirect()->route('subscribe');
            }
        }

        return $next($request);
    }
}
