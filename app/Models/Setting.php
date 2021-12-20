<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory, CrudTrait;

    protected $guarded = ['id'];
    public $timestamps = false;

    const NEXT_SEND_DAYS = 'next_send_days';
    const SETUP_PAYMENT_AMOUNT = 'setup_payment_amount';
    const PAYMENT_AMOUNT = 'payment_amount';
    const SHOP_ID = 'shop_id';
    const SECRET_KEY = 'secret_key';

    public function settingName()
    {
        if ($this->name == self::NEXT_SEND_DAYS) return 'Кол-во дней для рассылки';
        else if ($this->name == self::SETUP_PAYMENT_AMOUNT) return 'Сумма для привязки карты';
        else if ($this->name == self::PAYMENT_AMOUNT) return 'Сумма снятия';
        else if ($this->name == self::SHOP_ID) return 'ID магазина';
        else if ($this->name == self::SECRET_KEY) return 'Секретный ключ магазина';
    }
}
