<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    public $fillable = [
        'company_id',
        'name',
        'description',
        'start_time',
        'performance_duration',
        'break_duration',
        'is_active'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function questions()
    {
        return $this->hasMany(VotingQuestion::class)->orderBy('order');
    }
}
