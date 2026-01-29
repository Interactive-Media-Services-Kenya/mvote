<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'description',
        'settings',
        'default_voting_duration'
    ];

    protected $casts = [
        'settings' => 'array'
    ];

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
