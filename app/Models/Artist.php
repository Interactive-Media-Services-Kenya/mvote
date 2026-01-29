<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    public $fillable = [
        'user_id',
        'name',
        'phone',
        'bio',
        'photo',
        'is_active',
        'genre_id'
    ];
}
