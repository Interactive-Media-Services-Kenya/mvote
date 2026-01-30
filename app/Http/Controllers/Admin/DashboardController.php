<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Vote;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $liveArtist = Artist::with('genre')->where('status', 'live')->first();
        
        // Map live artist data for frontend
        $mappedLiveArtist = $liveArtist ? [
            'id' => $liveArtist->id,
            'name' => $liveArtist->name,
            'image' => $liveArtist->photo ?? 'https://via.placeholder.com/150',
            'status' => $liveArtist->status,
        ] : null;

        // Fetch some basic stats (mocked or real)
        $stats = [
            [
                'label' => 'Total Votes Cast',
                'value' => number_format(Vote::count()),
                'trend' => '+0%', // Placeholder
                'color' => 'text-brand-yellow',
            ],
            [
                'label' => 'Active Fans',
                'value' => '0', // Placeholder
                'trend' => 'Live',
                'color' => 'text-green-500',
            ],
            [
                'label' => 'Avg Rating',
                'value' => '0.0', // Placeholder
                'trend' => 'Stars',
                'color' => 'text-white'
            ],
        ];

        return Inertia::render('Admin/Dashboard', [
            'liveArtist' => $mappedLiveArtist,
            'stats' => $stats,
        ]);
    }
}
