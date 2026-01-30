<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LineupController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user) return redirect()->route('login');
        
        $role = $user->role->name ?? 'fan';
        $target = $role === 'judge' ? 'judge' : 'fan';

        $event = Event::where('is_active', true)->latest()->first();
        if ($event) {
            $event->load(['questions' => function($query) use ($target) {
                $query->whereIn('target', [$target, 'both'])->orderBy('order');
            }]);
        }
        
        $activePerformance = \App\Models\Performance::where('status', 'live')
            ->where('event_id', $event->id)
            ->first();

        $artists = \App\Models\Artist::with('genre')->where('is_active', true)->get()->map(function($artist) use ($activePerformance) {
            $isLive = $activePerformance && $activePerformance->artist_id === $artist->id;
            
            return [
                'id' => $artist->id,
                'name' => $artist->name,
                'genre' => $artist->genre->title ?? 'Unknown',
                'image' => $artist->photo ?? 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($artist->name),
                'status' => $isLive ? 'live' : $artist->status, // Pull status from DB
                'voteCount' => 0, 
                'voting_started_at' => $isLive && $activePerformance->voting_started_at ? $activePerformance->voting_started_at->toISOString() : null,
                'voting_ends_at' => $isLive && $activePerformance->voting_ends_at ? $activePerformance->voting_ends_at->toISOString() : null,
                'is_voting_paused' => $isLive ? $activePerformance->is_voting_paused : false,
                'performance_id' => $isLive ? $activePerformance->id : null,
            ];
        });
        
        return Inertia::render('Lineup', [
            'event' => $event,
            'artists' => $artists,
            'activePerformance' => $activePerformance ? [
                'id' => $activePerformance->id,
                'artist_id' => $activePerformance->artist_id,
                'voting_started_at' => $activePerformance->voting_started_at ? $activePerformance->voting_started_at->toISOString() : null,
                'voting_ends_at' => $activePerformance->voting_ends_at ? $activePerformance->voting_ends_at->toISOString() : null,
                'is_voting_paused' => $activePerformance->is_voting_paused,
            ] : null
        ]);
    }

    public function artist($id)
    {
        $user = auth()->user();
        if (!$user) return redirect()->route('login');
        
        $role = $user->role->name ?? 'fan';
        $target = $role === 'judge' ? 'judge' : 'fan';

        $event = Event::where('is_active', true)->latest()->first();
        if ($event) {
            $event->load(['questions' => function($query) use ($target) {
                $query->whereIn('target', [$target, 'both'])->orderBy('order');
            }]);
        }
        $artistModel = \App\Models\Artist::with('genre')->findOrFail($id);
        
        $activePerformance = \App\Models\Performance::where('status', 'live')
            ->where('event_id', $event->id)
            ->first();

        $isLive = $activePerformance && $activePerformance->artist_id === $artistModel->id;

        $artist = [
            'id' => $artistModel->id,
            'name' => $artistModel->name,
            'genre' => $artistModel->genre->title ?? 'Unknown',
            'image' => $artistModel->photo ?? 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($artistModel->name),
            'bio' => $artistModel->bio,
            'status' => $isLive ? 'live' : $artistModel->status,
            'scheduledTime' => '21:00', // Placeholder
            'discography' => [], 
            'voting_started_at' => $isLive && $activePerformance->voting_started_at ? $activePerformance->voting_started_at->toISOString() : null,
            'voting_ends_at' => $isLive && $activePerformance->voting_ends_at ? $activePerformance->voting_ends_at->toISOString() : null,
            'is_voting_paused' => $isLive ? $activePerformance->is_voting_paused : false,
            'performance_id' => $isLive ? $activePerformance->id : null,
        ];

        return Inertia::render('ArtistProfile', [
            'artist' => $artist,
            'event' => $event,
            'activePerformance' => $activePerformance ? [
                'id' => $activePerformance->id,
                'artist_id' => $activePerformance->artist_id,
                'voting_started_at' => $activePerformance->voting_started_at ? $activePerformance->voting_started_at->toISOString() : null,
                'voting_ends_at' => $activePerformance->voting_ends_at ? $activePerformance->voting_ends_at->toISOString() : null,
                'is_voting_paused' => $activePerformance->is_voting_paused,
            ] : null
        ]);
    }
}
