<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Performance;
use App\Models\Vote;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $activePerformance = Performance::with('artist.genre')
            ->where('status', 'live')
            ->first();
        
        $upcomingArtists = Artist::with(['genre', 'performances' => function($query) {
                $query->latest();
            }])
            ->where('is_active', true)
            ->where('status', '!=', 'live')
            ->orderBy('lineup_order')
            ->get()
            ->map(function($artist) {
                $performance = $artist->performances->first();
                $voterLogs = [];
                $fanVoters = 0;
                $judgeVoters = 0;
                if ($performance) {
                    $fanVoters = Vote::where('performance_id', $performance->id)
                        ->whereHas('user.role', fn($q) => $q->where('name', 'fan'))
                        ->distinct('user_id')
                        ->count('user_id');
                    
                    $judgeVoters = Vote::where('performance_id', $performance->id)
                        ->whereHas('user.role', fn($q) => $q->where('name', 'judge'))
                        ->distinct('user_id')
                        ->count('user_id');

                    if ($artist->status === 'closed') {
                        $voterLogs = Vote::where('performance_id', $performance->id)
                            ->with('user')
                            ->get()
                            ->groupBy('user_id')
                            ->map(function($userVotes) use ($performance) {
                                $user = $userVotes->first()->user;
                                return [
                                    'nickname' => $user->nick_name ?? 'Anonymous',
                                    'role' => $user->role->name ?? 'fan',
                                    'points' => $performance->userRatedPoints($user),
                                    'max' => $performance->maxPossiblePoints($user)
                                ];
                            })->values();
                    }
                }

                return [
                    'id' => $artist->id,
                    'name' => $artist->name,
                    'status' => $artist->status,
                    'photo' => $artist->photo,
                    'performance_id' => $performance?->id,
                    'fan_voters' => $fanVoters,
                    'judge_voters' => $judgeVoters,
                    'voter_logs' => $voterLogs,
                ];
            });

        // Map live artist data for frontend
        $mappedLiveArtist = $activePerformance ? [
            'id' => $activePerformance->artist->id,
            'performance_id' => $activePerformance->id,
            'name' => $activePerformance->artist->name,
            'image' => $activePerformance->artist->photo ?? 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($activePerformance->artist->name),
            'status' => 'live',
            'voting_started_at' => $activePerformance->voting_started_at ? $activePerformance->voting_started_at->toISOString() : null,
            'voting_ends_at' => $activePerformance->voting_ends_at ? $activePerformance->voting_ends_at->toISOString() : null,
            'is_voting_paused' => $activePerformance->is_voting_paused,
            'fan_voters' => Vote::where('performance_id', $activePerformance->id)
                ->whereHas('user.role', fn($q) => $q->where('name', 'fan'))
                ->distinct('user_id')
                ->count('user_id'),
            'judge_voters' => Vote::where('performance_id', $activePerformance->id)
                ->whereHas('user.role', fn($q) => $q->where('name', 'judge'))
                ->distinct('user_id')
                ->count('user_id'),
        ] : null;

        // Fetch some basic stats
        $stats = [
            [
                'label' => 'Total Participants',
                'value' => number_format(Vote::distinct('user_id')->count('user_id')),
                'trend' => '+0%', 
                'color' => 'text-brand-yellow',
            ],
            [
                'label' => 'Active Fans',
                'value' => number_format(\Illuminate\Support\Facades\DB::table('sessions')
                    ->join('users', 'sessions.user_id', '=', 'users.id')
                    ->join('roles', 'users.role_id', '=', 'roles.id')
                    ->where('roles.name', 'fan')
                    ->where('last_activity', '>=', now()->subMinutes(5)->getTimestamp())
                    ->count()), 
                'trend' => 'Live',
                'color' => 'text-green-500',
            ],
            [
                'label' => 'Active Judges',
                'value' => number_format(\Illuminate\Support\Facades\DB::table('sessions')
                    ->join('users', 'sessions.user_id', '=', 'users.id')
                    ->join('roles', 'users.role_id', '=', 'roles.id')
                    ->where('roles.name', 'judge')
                    ->where('last_activity', '>=', now()->subMinutes(5)->getTimestamp())
                    ->count()), 
                'trend' => 'Real-time',
                'color' => 'text-blue-500',
            ],
            [
                'label' => 'Avg Rating',
                'value' => $activePerformance ? number_format($activePerformance->average_score, 1) : '0.0', 
                'trend' => 'Stars',
                'color' => 'text-white'
            ],
        ];

        return Inertia::render('Admin/Dashboard', [
            'liveArtist' => $mappedLiveArtist,
            'stats' => $stats,
            'upcomingArtists' => $upcomingArtists,
        ]);
    }
}
