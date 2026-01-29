<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Performances extends Model
{
    public $fillable = [
        'artist_id',
        'event_id',
        'status',
        'start_time',
        'end_time'
    ];
}
