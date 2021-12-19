<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('index');

Route::get('save', 'App\Http\Controllers\PaymentController@setPayments')->name('saveyandex');
Route::post('notification', 'App\Http\Controllers\PaymentController@paymentNotification')->name('notificationyandex');

Route::post('register', 'App\Http\Controllers\AuthController@register')->name('register');
Route::post('login', 'App\Http\Controllers\AuthController@login')->name('login');
Route::post('logout', 'App\Http\Controllers\AuthController@logout')->name('logout');
