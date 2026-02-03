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
        $votes = $this->votes()
            ->with(['user.role', 'question' => function($q) {
                $q->where('type', 'rating');
            }])
            ->get()
            ->filter(function($vote) {
                return $vote->question && $vote->question->type === 'rating';
            });

        if ($votes->isEmpty()) {
            return [
                'average_points' => 0,
                'max_points' => 0,
                'average_percentage' => 0
            ];
        }

        // We need to calculate the average points across all users.
        // Each user has a different "max points" if they are a judge vs a fan (though usually they are the same for rating questions).
        
        $userScores = $votes->groupBy('user_id')->map(function ($userVotes) {
            $user = $userVotes->first()->user;
            return [
                'points' => $userVotes->sum('rating'),
                'max' => $this->maxPossiblePoints($user)
            ];
        });

        $totalPoints = $userScores->sum('points');
        $totalMaxPoints = $userScores->sum('max');
        $avgPoints = $userScores->avg('points');
        $avgMax = $userScores->avg('max');

        return [
            'average_points' => round($avgPoints, 1),
            'max_points' => round($avgMax, 1),
            'average_percentage' => $totalMaxPoints > 0 ? round(($totalPoints / $totalMaxPoints) * 100, 1) : 0
        ];
    }

    public function getAverageScoreAttribute()
    {
        $data = $this->getGlobalRatingData();
        return $data['average_points'];
    }
}
