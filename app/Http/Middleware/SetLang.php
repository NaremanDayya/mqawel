<?php

namespace App\Http\Middleware;

use App\Models\Master;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLang
{
    public function handle(Request $request, Closure $next): Response
    {
        if(!session()->has('current_lang')){
            session()->put('current_lang', 'ar');
        }

        app()->setLocale(session('current_lang', 'ar'));

        $User= Auth::user();

        if($User){
            if($User->getTable() == 'users'){
                $User= User::find(Auth::user()->id);

                if($User){
                    $User->locale= session('current_lang');
                    $User->save();
                }
            }
            else if($User->getTable() == 'masters'){
                $User= Master::find(Auth::user()->id);

                if($User){
                    $User->locale= session('current_lang');
                    $User->save();
                }
            }
        }

        return $next($request);
    }
}
