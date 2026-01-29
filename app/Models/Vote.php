<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $fillable = [
        'user_id',
        'performance_id',
        'question_id',
        'rating',
        'comment'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function performance()
    {
        return $this->belongsTo(Performance::class);
    }

    public function question()
    {
        return $this->belongsTo(VotingQuestion::class, 'question_id');
    }
}
