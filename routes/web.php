<?php

use App\Factories\PaymentSystemFactory;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('index');

Route::get('save', 'App\Http\Controllers\PaymentController@setPayments')->name('save');
Route::post('notification', 'App\Http\Controllers\PaymentController@paymentNotification')->name('notification');
Route::get('charge', function () {
    $service = PaymentSystemFactory::all()[config('packages.payment_system')];
    $service->charge(\App\Models\User::find(1));
});

//Route::post('register', 'App\Http\Controllers\AuthController@register')->name('register');
//Route::post('login', 'App\Http\Controllers\AuthController@login')->name('login');
//Route::get('logout', 'App\Http\Controllers\AuthController@logout')->name('logout');
