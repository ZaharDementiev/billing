<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory, CrudTrait;

    protected $guarded = ['id'];

    const YANDEX = 0;
    const CLOUD = 1;

    public const PAID = 0;
    public const WAITING = 1;
    public const REJECTED = 2;
    public const REFUNDED = 3;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function userContact()
    {
        $user = $this->user;

        return $user->name . ', ' . $user->email;
    }

    public function statusName()
    {
        if ($this->status == self::WAITING) {
            return 'В ожидании';
        } elseif ($this->status == self::PAID) {
            return 'Оплачено';
        } elseif ($this->status == self::REJECTED) {
            return 'Отклонено';
        } elseif ($this->status == self::REFUNDED) {
            return 'Возвращено';
        }
    }
}
