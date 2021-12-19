<?php

namespace App\Factories;

use App\Models\Payment;
use App\Services\YandexPaymentService;

class PaymentSystemFactory
{
    public static function all()
    {
        return [
            Payment::YANDEX => new YandexPaymentService(),
        ];
    }
}
