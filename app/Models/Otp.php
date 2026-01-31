<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    public $fillable = [
        'user_id',
        'code',
        'expires_at'
    ];
}
