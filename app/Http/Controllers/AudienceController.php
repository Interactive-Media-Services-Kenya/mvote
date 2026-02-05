<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Performance;
use App\Models\Vote;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class AudienceController extends Controller
{
    protected $rankingService;

    public function __construct(\App\Services\RankingService $rankingService)
    {
        $this->rankingService = $rankingService;
    }

    public function index()
    {
        $event = Event::where('is_active', true)->latest()->first();
        if (!$event) {
            return Inertia::render('AudienceDisplay', [
                'activePerformance' => null,
                'stats' => $this->getStats()
            ]);
        }

        $performance = Performance::latest()->first();
        $rankings = $performance ? $this->rankingService->getEventRankings($performance->id) : collect();

        // $rankings = $this->rankingService->getEventRankings($event->id);

        $activePerformance = Performance::with('artist.genre')
            ->where('event_id', $event->id)
            ->where('status', 'live')
            ->first();

        $performanceDataArray = null;
        if ($activePerformance) {
            // print ("<pre>");
            // print_r($activePerformance);
            exit;
            $performanceData = $activePerformance->getGlobalRatingData();
            $voteCount = Vote::where('performance_id', $activePerformance->id)->distinct('user_id')->count('user_id');

            // $scoreData = $rankings->firstWhere('performance_id', $activePerformance->id);
            $scoreData = $activePerformance ? $this->rankingService->getEventRankings($activePerformance->id) : null;

            $performanceDataArray = [
                'id' => $activePerformance->id,
                'artist_name' => $activePerformance->artist->name,
                'artist_image' => $activePerformance->artist->photo ?? 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($activePerformance->artist->name),
                'genre' => $activePerformance->artist->genre->title ?? 'Unknown',
                'voteCount' => $voteCount,
                'finalScore' => number_format($scoreData?->bias_rating ?? 0, 1),
                'avgMax' => number_format($activePerformance->getEventMaxPoints(), 1),
                'voting_started_at' => $activePerformance->voting_started_at ? $activePerformance->voting_started_at->toISOString() : null,
                'voting_ends_at' => $activePerformance->voting_ends_at ? $activePerformance->voting_ends_at->toISOString() : null,
                'is_voting_paused' => $activePerformance->is_voting_paused,
            ];
        }

        $leaderboard = Performance::with('artist')
            ->where('event_id', $event->id)
            ->whereIn('status', ['closed', 'live'])
            ->get()
            ->map(function ($performance) use ($rankings) {
                $scoreData = $rankings->firstWhere('performance_id', $performance->id);
                return [
                    'id' => $performance->artist_id,
                    'name' => $performance->artist->name,
                    'score' => $scoreData ? round($scoreData->bias_rating, 1) : 0,
                    'image' => $performance->artist->photo ?? 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($performance->artist->name),
                ];
            })
            ->sortByDesc('score')
            ->values();

        return Inertia::render('AudienceDisplay', [
            'event' => $event,
            'activePerformance' => $performanceDataArray,
            'leaderboard' => $leaderboard,
            'stats' => $this->getStats()
        ]);
    }

    private function getStats()
    {
        // Active fans: users with a session in the last 5 minutes (ignoring admins for now, or filter by role)
        // Note: This requires the session driver to be 'database' or 'redis' and session table to be configured.
        // Assuming database driver as per implementation plan.
        $activeFans = DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('roles.name', 'fan')
            ->where('last_activity', '>=', now()->subMinutes(5)->getTimestamp())
            ->count();

        return [
            'activeFans' => $activeFans,
            'totalVotes' => Vote::distinct('user_id')->count('user_id'),
        ];
    }
}
