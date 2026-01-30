<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    public $fillable = [
        'company_id',
        'name',
        'description',
        'is_active'
    ];

    public function questions()
    {
        return $this->hasMany(VotingQuestion::class)->orderBy('order');
    }
}
