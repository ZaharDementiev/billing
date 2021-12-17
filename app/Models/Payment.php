<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    const YANDEX = 0;

    const SETUP_PAYMENT_VALUE = 1;
    const PAYMENT_VALUE = 1;
}
