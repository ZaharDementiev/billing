<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('index');

Route::get('save', 'App\Http\Controllers\PaymentController@setPayments')->name('saveyandex');
Route::post('notification', 'App\Http\Controllers\PaymentController@paymentNotification')->name('notificationyandex');
