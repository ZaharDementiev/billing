<?php

use App\Factories\PaymentSystemFactory;
use App\Jobs\SendEmailJob;
use App\Mail\SendEmail;
use Illuminate\Support\Facades\Mail;
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


Route::get('123', function () {
//    Mail::to('zahardementiev@gmail.com')->send(new SendEmail(\App\Models\Email::first()));
    dispatch(new SendEmailJob('zahardementiev@gmail.com', \App\Models\Email::first()))->onQueue('mailing');
});

Route::get('deleteUser', 'App\Http\Controllers\PaymentController@deleteUser')->name('dropUser');
Route::post('sendMail', 'App\Http\Controllers\EmailController@sendToUser')->name('sendMail');

//Route::post('register', 'App\Http\Controllers\AuthController@register')->name('register');
//Route::post('login', 'App\Http\Controllers\AuthController@login')->name('login');
//Route::get('logout', 'App\Http\Controllers\AuthController@logout')->name('logout');
