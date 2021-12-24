<?php

namespace App\Http\Controllers;

use App\Factories\PaymentSystemFactory;
use App\Models\User;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    private $service;

    public function __construct()
    {
        $this->service = PaymentSystemFactory::all()[1];
    }

    public function setPayments(Request $request)
    {
        return $this->service->setup($request->input('email'));
    }

    public function paymentNotification(Request $request)
    {
        return $this->service->notify($request->all());
    }

    public function deleteUser(Request $request)
    {
        return User::where('email', $request->input('email'))->delete();
    }
}
