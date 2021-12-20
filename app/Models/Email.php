<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory, CrudTrait;

    protected $guarded = ['id'];
    protected $casts = ['attachments'  => 'array'];
    public $timestamps = false;
}
