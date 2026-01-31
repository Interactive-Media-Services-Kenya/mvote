<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Event;
use App\Models\Performance;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LineupController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user)
            return redirect()->route('login');

        $role = $user->role->name ?? 'fan';
        $target = $role === 'judge' ? 'judge' : 'fan';

        $event = Event::where('is_active', true)->latest()->first();
        if ($event) {
            $event->load([
                'questions' => function ($query) use ($target) {
                    $query->whereIn('target', [$target, 'both'])->orderBy('order');
                }
            ]);
        }

        $activePerformance = Performance::where('status', 'live')
            ->where('event_id', $event->id)
            ->first();

        $artists = Artist::with('genre')->where('is_active', true)->get()->map(function ($artist) use ($activePerformance, $user) {
            $isLive = $activePerformance && $activePerformance->artist_id === $artist->id;

            $hasVoted = false;
            if ($isLive && $user) {
                $hasVoted = Vote::where('user_id', $user->id)
                    ->where('performance_id', $activePerformance->id)
                    ->exists();
            }

            return [
                'id' => $artist->id,
                'name' => $artist->name,
                'genre' => $artist->genre->title ?? 'Unknown',
                'image' => $artist->photo ?? 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($artist->name),
                'status' => $isLive ? 'live' : $artist->status,
                'voteCount' => 0,
                'voting_started_at' => $isLive && $activePerformance->voting_started_at ? $activePerformance->voting_started_at->toISOString() : null,
                'voting_ends_at' => $isLive && $activePerformance->voting_ends_at ? $activePerformance->voting_ends_at->toISOString() : null,
                'is_voting_paused' => $isLive ? $activePerformance->is_voting_paused : false,
                'performance_id' => $isLive ? $activePerformance->id : null,
                'hasVoted' => $hasVoted,
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
                'hasVoted' => Vote::where('user_id', $user->id)->where('performance_id', $activePerformance->id)->exists(),
            ] : null
        ]);
    }

    public function artist($id)
    {
        $user = Auth::user();
        if (!$user)
            return redirect()->route('login');

        $role = $user->role->name ?? 'fan';
        $target = $role === 'judge' ? 'judge' : 'fan';

        $event = Event::where('is_active', true)->latest()->first();
        if ($event) {
            $event->load([
                'questions' => function ($query) use ($target) {
                    $query->whereIn('target', [$target, 'both'])->orderBy('order');
                }
            ]);
        }
        $artistModel = Artist::with('genre')->findOrFail($id);

        $activePerformance = Performance::where('status', 'live')
            ->where('event_id', $event->id)
            ->first();

        $isLive = $activePerformance && $activePerformance->artist_id === $artistModel->id;
        $hasVoted = false;
        if ($isLive && $user) {
            $hasVoted = Vote::where('user_id', $user->id)
                ->where('performance_id', $activePerformance->id)
                ->exists();
        }

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
            'hasVoted' => $hasVoted,
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
                'hasVoted' => $hasVoted,
            ] : null
        ]);
    }
}
