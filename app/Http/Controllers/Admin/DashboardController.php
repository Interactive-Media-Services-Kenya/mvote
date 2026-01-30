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
            ->orderByRaw("status = 'upcoming' DESC")
            ->get()
            ->map(function($artist) {
                return [
                    'id' => $artist->id,
                    'name' => $artist->name,
                    'status' => $artist->status,
                    'photo' => $artist->photo,
                    'performance_id' => $artist->performances->first()?->id,
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
        ] : null;

        // Fetch some basic stats
        $stats = [
            [
                'label' => 'Total Votes Cast',
                'value' => number_format(Vote::count()),
                'trend' => '+0%', 
                'color' => 'text-brand-yellow',
            ],
            [
                'label' => 'Active Fans',
                'value' => '0', 
                'trend' => 'Live',
                'color' => 'text-green-500',
            ],
            [
                'label' => 'Avg Rating',
                'value' => '0.0', 
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
