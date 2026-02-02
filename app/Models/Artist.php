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
        'lineup_order',
        'genre_id'
    ];

    public function genre(){
        return $this->belongsTo(Genre::class);
    }

    public function performances()
    {
        return $this->hasMany(Performance::class);
    }

    public function getScheduledTimeAttribute()
    {
        $event = Event::where('is_active', true)->latest()->first();
        if (!$event || !$event->start_time) {
            return null;
        }

        // Calculation: Event Start Time + (Position * (Performance Duration + Break Duration))
        // Position is 0-indexed based on lineup_order
        // We'll get all active artists ordered by lineup_order to find the rank
        $artists = self::where('is_active', true)->orderBy('lineup_order')->pluck('id')->toArray();
        $rank = array_search($this->id, $artists);
        
        if ($rank === false) {
            return null;
        }

        $totalMinutes = $rank * ($event->performance_duration + $event->break_duration);
        
        return $event->start_time->addMinutes($totalMinutes);
    }
}
