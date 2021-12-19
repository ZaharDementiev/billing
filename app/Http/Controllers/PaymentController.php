<?php

namespace App\Http\Controllers;

use App\Factories\PaymentSystemFactory;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    private $service;

    public function __construct()
    {
        $this->service = PaymentSystemFactory::all()[config('packages.payment_system')];
    }

    public function setPayments()
    {
        return $this->service->setup(auth()->check(), auth()->user());
    }

    public function paymentNotification(Request $request)
    {
        return $this->service->notify($request->all());
    }
}
