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
    public function index()
    {
        $event = Event::where('is_active', true)->latest()->first();
        if (!$event) {
            return Inertia::render('AudienceDisplay', [
                'activePerformance' => null,
                'stats' => $this->getStats()
            ]);
        }

        $activePerformance = Performance::with('artist.genre')
            ->where('event_id', $event->id)
            ->where('status', 'live')
            ->first();

        $performanceData = null;
        if ($activePerformance) {
            $voteCount = Vote::where('performance_id', $activePerformance->id)->count();
            $avgRating = Vote::where('performance_id', $activePerformance->id)->avg('rating') ?: 0;

            $performanceData = [
                'id' => $activePerformance->id,
                'artist_name' => $activePerformance->artist->name,
                'artist_image' => $activePerformance->artist->photo ?? 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($activePerformance->artist->name),
                'genre' => $activePerformance->artist->genre->title ?? 'Unknown',
                'voteCount' => $voteCount,
                'avgRating' => number_format($avgRating, 1),
                'voting_started_at' => $activePerformance->voting_started_at ? $activePerformance->voting_started_at->toISOString() : null,
                'voting_ends_at' => $activePerformance->voting_ends_at ? $activePerformance->voting_ends_at->toISOString() : null,
                'is_voting_paused' => $activePerformance->is_voting_paused,
            ];
        }

        return Inertia::render('AudienceDisplay', [
            'activePerformance' => $performanceData,
            'stats' => $this->getStats()
        ]);
    }

    private function getStats()
    {
        // Active fans: users with a session in the last 5 minutes (ignoring admins for now, or filter by role)
        // Note: This requires the session driver to be 'database' or 'redis' and session table to be configured.
        // Assuming database driver as per implementation plan.
        $activeFans = DB::table('sessions')
            ->where('last_activity', '>=', now()->subMinutes(5)->getTimestamp())
            ->count();

        return [
            'activeFans' => $activeFans,
            'totalVotes' => Vote::count(),
        ];
    }
}
