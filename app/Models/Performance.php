<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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

    public function maxPossiblePoints(User $user)
    {
        $role = $user->role->name ?? 'fan';
        $target = in_array($role, ['judge', 'admin']) ? 'judge' : 'fan';

        $ratingQuestionsCount = $this->event->questions()
            ->where('type', 'rating')
            ->whereIn('target', [$target, 'both'])
            ->count();

        return $ratingQuestionsCount * 5; // Each question has a max of 5 points
    }

    public function userRatedPoints(User $user)
    {
        return $this->votes()
            ->where('user_id', $user->id)
            ->whereHas('question', function ($query) {
                $query->where('type', 'rating');
            })
            ->sum('rating');
    }

    public function getGlobalRatingData()
    {
        $score = app(\App\Services\RankingService::class)->getPerformanceScore($this);
        $max = $this->getEventMaxPoints();

        return [
            'average_points' => $score,
            'max_points' => $max,
            'average_percentage' => $max > 0 ? round(($score / $max) * 100, 1) : 0
        ];
    }

    public function getAverageScoreAttribute()
    {
        $data = $this->getGlobalRatingData();
        return $data['average_points'];
    }

    public function getParticipationWeightedScore()
    {
        return app(\App\Services\RankingService::class)->getPerformanceScore($this);
    }

    public function getEventMaxPoints()
    {
        return $this->event->questions()
            ->where('type', 'rating')
            ->count() * 5;
    }
}
