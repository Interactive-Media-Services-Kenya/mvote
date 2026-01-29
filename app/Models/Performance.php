<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Performance extends Model
{
    protected $fillable = [
        'artist_id',
        'event_id',
        'status',
        'start_time',
        'end_time',
        'voting_started_at',
        'voting_ends_at',
        'is_voting_paused'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'voting_started_at' => 'datetime',
        'voting_ends_at' => 'datetime',
        'is_voting_paused' => 'boolean'
    ];

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }
}
