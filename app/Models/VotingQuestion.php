<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VotingQuestion extends Model
{
    protected $fillable = [
        'event_id',
        'question_text',
        'type',
        'low_label',
        'high_label',
        'target',
        'order'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class, 'question_id');
    }
}
