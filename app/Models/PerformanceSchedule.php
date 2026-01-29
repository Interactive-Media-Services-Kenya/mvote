<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceSchedule extends Model
{
    protected $fillable = [
        'event_id',
        'artist_id',
        'scheduled_start_time',
        'duration_minutes'
    ];

    protected $casts = [
        'scheduled_start_time' => 'datetime'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }
}
