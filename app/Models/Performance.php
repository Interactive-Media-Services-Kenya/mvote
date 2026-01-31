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

    public function getAverageScoreAttribute()
    {
        $votes = $this->votes()->with(['user.role', 'question'])->get();
        if ($votes->isEmpty()) {
            return 0;
        }

        $event = $this->event;
        // Total RATING questions available for each role
        $totalFanQuestions = $event->questions()
            ->where('type', 'rating')
            ->whereIn('target', ['fan', 'both'])
            ->count();
        $totalJudgeQuestions = $event->questions()
            ->where('type', 'rating')
            ->whereIn('target', ['judge', 'both'])
            ->count();

        $voterScores = $votes->groupBy('user_id')->map(function ($userVotes) use ($totalFanQuestions, $totalJudgeQuestions) {
            $user = $userVotes->first()->user;
            $role = $user->role->name ?? 'fan';
            $target = $role === 'judge' ? 'judge' : 'fan';
            
            $totalQuestions = ($target === 'judge') ? $totalJudgeQuestions : $totalFanQuestions;
            
            if ($totalQuestions == 0) {
                return 0;
            }
            
            $sumRatings = $userVotes->sum('rating');
            return $sumRatings / $totalQuestions;
        });

        return $voterScores->average();
    }
}
