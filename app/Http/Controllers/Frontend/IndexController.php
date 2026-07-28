<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index(){
        return view('frontend.index');
    }

    public function submitContact(Request $request){
        $Contact= new Contact();
        $Contact->ip_address= $request->ip();
        $Contact->name= $request->name;
        $Contact->email= $request->email;
        $Contact->phone= $request->phone;
        $Contact->company_name= $request->company_name;
        $Contact->purpose= $request->purpose;
        $Contact->title= $request->title;
        $Contact->description= $request->description;
        $Contact->is_open= 0;
        $Contact->save();

        //Redirect to success page.

        return view('frontend.form-submitted');
    }
}
