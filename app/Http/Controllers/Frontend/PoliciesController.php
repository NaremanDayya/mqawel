<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class PoliciesController extends Controller
{
    public function index(Request $request){
        $key= $request->key;

        $Setting= Setting::find(1);

        $title= __('backend.'.$key);
        $content= $content= $Setting->{$key};

        return view('frontend.policy', compact('title', 'content'));
    }
}
