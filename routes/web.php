<?php

use App\Http\Controllers\Backend\SubscriptionsController;
use App\Http\Controllers\Frontend\AuthController;
use App\Http\Controllers\Frontend\IndexController;
use App\Http\Controllers\Frontend\PoliciesController;
use App\Http\Controllers\Frontend\ThawaniController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'lang'], function(){
	Route::get('/', [IndexController::class, 'index'])->name('index');
	
	Route::post('submit-contact', [IndexController::class, 'submitContact'])->name('submit-contact');

	Route::get('policies', [PoliciesController::class, 'index'])->name('policy');

	Route::get('subscribe', [SubscriptionsController::class, 'subscribe'])->name('subscribe');

	Route::get('subscribe/checkout', [SubscriptionsController::class, 'checkout'])->name('subscribe.checkout');

	Route::get('sign-up', [AuthController::class, 'sign_up'])->name('sign-up');
});

Route::get('lang/{lang}', function ($lang) {
	session()->put('current_lang', $lang);

	return redirect(url()->previousPath());
});

Route::post('sign-up-submit', [AuthController::class, 'sign_up_submit'])->name('sign-up-submit');

Route::get('logout', function (Request $request) {
    Auth::logout();

    return redirect()->to(route('index'));
})->name('logout');

Route::get('payment', [ThawaniController::class, 'payment'])->name('payment');
Route::get('payment-success', [ThawaniController::class, 'success'])->name('payment-success');
Route::get('payment-fail', [ThawaniController::class, 'fail'])->name('payment-fail');

Route::get('/link-storage', function () {
    Artisan::call('storage:link');
    return 'Storage link created successfully!';
});