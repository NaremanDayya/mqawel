<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function sign_up(Request $request){
        return view('frontend.signup');
    }

    public function sign_up_submit(Request $request){
        
    }
}
