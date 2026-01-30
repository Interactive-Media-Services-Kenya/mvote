<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'bio',
        'photo',
        'is_active',
        'status',
        'genre_id'
    ];

    public function genre(){
        return $this->belongsTo(Genre::class);
    }
}
