<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Event;
use App\Models\Performance;
use App\Models\Vote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LineupController extends Controller
{
    public function index(): RedirectResponse|Response
    {
        $user = Auth::user();
        if (!$user)
            return redirect()->route('login');

        $role = $user->role->name ?? 'fan';
        $target = in_array($role, ['judge', 'admin']) ? 'judge' : 'fan';

        $event = Event::where('is_active', true)->latest()->first();
        if ($event) {
            $event->load([
                'questions' => function ($query) use ($target, $role) {
                    $query->whereIn('target', [$target, 'both']);
                    if ($role === 'fan') {
                        $query->where('type', 'rating');
                    }
                    $query->orderBy('order');
                }
            ]);
        }

        $activePerformance = Performance::where('status', 'live')
            ->where('event_id', $event->id)
            ->first();

        $allPerformances = Performance::where('event_id', $event->id)->get();
        $userVotedPerformanceIds = $user 
            ? Vote::where('user_id', $user->id)
                ->whereIn('performance_id', $allPerformances->pluck('id'))
                ->distinct()
                ->pluck('performance_id')
                ->toArray()
            : [];

        $hasVotedActive = $activePerformance && in_array($activePerformance->id, $userVotedPerformanceIds);

        $artists = Artist::with('genre')
            ->where('is_active', true)
            ->get()
            ->map(function ($artist) use ($activePerformance, $user, $allPerformances, $userVotedPerformanceIds) {
                $performance = $allPerformances->where('artist_id', $artist->id)->first();
                $isLive = $activePerformance && $activePerformance->artist_id === $artist->id;

                $hasVoted = $performance && in_array($performance->id, $userVotedPerformanceIds);
                $voterRating = null;
                $globalRating = null;

                if ($hasVoted && $user && ($isLive || $artist->status === 'closed')) {
                    $voterRating = [
                        'points' => $performance->userRatedPoints($user),
                        'max' => $performance->maxPossiblePoints($user)
                    ];
                    $globalRating = $performance->getGlobalRatingData();
                }

                $statusRank = match ($isLive ? 'live' : $artist->status) {
                    'live' => 1,
                    'upcoming' => 2,
                    'closed' => 3,
                    default => 4,
                };

                $isPerforming = $isLive;
                $isVotingOpen = $isLive && 
                                $activePerformance->voting_started_at && 
                                ($activePerformance->voting_ends_at === null || $activePerformance->voting_ends_at->isFuture()) && 
                                !$activePerformance->is_voting_paused;

                return [
                    'id' => $artist->id,
                    'name' => $artist->name,
                    'genre' => $artist->genre->title ?? 'Unknown',
                    'image' => $artist->photo ?? 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($artist->name),
                    'status' => $isLive ? 'live' : $artist->status,
                    'is_performing' => $isPerforming,
                    'is_voting_open' => $isVotingOpen,
                    'status_rank' => $statusRank,
                    'lineup_order' => $artist->lineup_order,
                    'scheduled_time' => $artist->scheduled_time ? $artist->scheduled_time->format('H:i') : '--:--',
                    'voteCount' => 0,
                    'voting_started_at' => $isLive && $activePerformance->voting_started_at ? $activePerformance->voting_started_at->toISOString() : null,
                    'voting_ends_at' => $isLive && $activePerformance->voting_ends_at ? $activePerformance->voting_ends_at->toISOString() : null,
                    'is_voting_paused' => $isLive ? $activePerformance->is_voting_paused : false,
                    'performance_id' => $performance ? $performance->id : null,
                    'hasVoted' => $hasVoted,
                    'voterRating' => $voterRating,
                    'globalRating' => $globalRating,
                ];
            })
            ->sort(function ($a, $b) {
                if ($a['status_rank'] === $b['status_rank']) {
                    return $a['lineup_order'] <=> $b['lineup_order'];
                }
                return $a['status_rank'] <=> $b['status_rank'];
            })
            ->values();

        return Inertia::render('Lineup', [
            'event' => $event,
            'artists' => $artists,
            'userRole' => $role,
            'activePerformance' => $activePerformance ? [
                'id' => $activePerformance->id,
                'artist_id' => $activePerformance->artist_id,
                'voting_started_at' => $activePerformance->voting_started_at ? $activePerformance->voting_started_at->toISOString() : null,
                'voting_ends_at' => $activePerformance->voting_ends_at ? $activePerformance->voting_ends_at->toISOString() : null,
                'is_performing' => true,
                'is_voting_open' => $activePerformance->voting_started_at && 
                                    ($activePerformance->voting_ends_at === null || $activePerformance->voting_ends_at->isFuture()) && 
                                    !$activePerformance->is_voting_paused,
                'voterRating' => ($hasVotedActive) ? [
                    'points' => $activePerformance->userRatedPoints($user),
                    'max' => $activePerformance->maxPossiblePoints($user)
                ] : null,
                'globalRating' => ($hasVotedActive) ? $activePerformance->getGlobalRatingData() : null,
            ] : null
        ]);
    }

    public function artist($id): RedirectResponse|Response
    {
        $user = Auth::user();
        if (!$user)
            return redirect()->route('login');

        $role = $user->role->name ?? 'fan';
        $target = in_array($role, ['judge', 'admin']) ? 'judge' : 'fan';

        $event = Event::where('is_active', true)->latest()->first();
        if ($event) {
            $event->load([
                'questions' => function ($query) use ($target, $role) {
                    $query->whereIn('target', [$target, 'both']);
                    if ($role === 'fan') {
                        $query->where('type', 'rating');
                    }
                    $query->orderBy('order');
                }
            ]);
        }
        $artistModel = Artist::with(['genre', 'discography' => function($q) {
            $q->orderBy('release_year', 'desc');
        }])->findOrFail($id);

        $activePerformance = Performance::where('status', 'live')
            ->where('event_id', $event->id)
            ->first();

        $isLive = $activePerformance && $activePerformance->artist_id === $artistModel->id;
        $hasVoted = false;
        $voterRating = null;
        $globalRating = null;

        if ($isLive && $user) {
            $hasVoted = Vote::where('user_id', $user->id)
                ->where('performance_id', $activePerformance->id)
                ->exists();

            if ($hasVoted) {
                $voterRating = [
                    'points' => $activePerformance->userRatedPoints($user),
                    'max' => $activePerformance->maxPossiblePoints($user)
                ];
                $globalRating = $activePerformance->getGlobalRatingData();
            }
        }

        $isPerforming = $isLive;
        $isVotingOpen = $isLive && 
                        $activePerformance->voting_started_at && 
                        ($activePerformance->voting_ends_at === null || $activePerformance->voting_ends_at->isFuture()) && 
                        !$activePerformance->is_voting_paused;

        $artist = [
            'id' => $artistModel->id,
            'name' => $artistModel->name,
            'genre' => $artistModel->genre->title ?? 'Unknown',
            'image' => $artistModel->photo ?? 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($artistModel->name),
            'bio' => $artistModel->bio,
            'status' => $isLive ? 'live' : $artistModel->status,
            'is_performing' => $isPerforming,
            'is_voting_open' => $isVotingOpen,
            'scheduledTime' => $artistModel->scheduled_time ? $artistModel->scheduled_time->format('H:i') : '--:--',
            'discography' => $artistModel->discography->map(function($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'year' => $item->release_year,
                    'description' => $item->description,
                    'link' => $item->link,
                ];
            }),
            'voting_started_at' => $isLive && $activePerformance->voting_started_at ? $activePerformance->voting_started_at->toISOString() : null,
            'voting_ends_at' => $isLive && $activePerformance->voting_ends_at ? $activePerformance->voting_ends_at->toISOString() : null,
            'is_voting_paused' => $isLive ? $activePerformance->is_voting_paused : false,
            'performance_id' => $isLive ? $activePerformance->id : null,
            'hasVoted' => $hasVoted,
            'voterRating' => $voterRating,
            'globalRating' => $globalRating,
        ];

        return Inertia::render('ArtistProfile', [
            'artist' => $artist,
            'event' => $event,
            'activePerformance' => $activePerformance ? [
                'id' => $activePerformance->id,
                'artist_id' => $activePerformance->artist_id,
                'voting_started_at' => $activePerformance->voting_started_at ? $activePerformance->voting_started_at->toISOString() : null,
                'voting_ends_at' => $activePerformance->voting_ends_at ? $activePerformance->voting_ends_at->toISOString() : null,
                'is_performing' => true,
                'is_voting_open' => $isVotingOpen,
                'is_voting_paused' => $activePerformance->is_voting_paused,
                'hasVoted' => $hasVoted,
                'voterRating' => $voterRating,
                'globalRating' => $globalRating,
            ] : null
        ]);
    }
}
