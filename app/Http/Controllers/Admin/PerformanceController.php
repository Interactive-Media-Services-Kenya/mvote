<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Performance;
use App\Models\Event;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PerformanceController extends Controller
{
    public function start(Request $request)
    {
        $request->validate([
            'artist_id' => 'required|exists:artists,id',
        ]);

        $event = Event::where('is_active', true)->first();
        if (!$event) {
            return back()->with('error', 'No active event found.');
        }

        // Close any currently live performances
        Performance::where('event_id', $event->id)
            ->where('status', 'live')
            ->update(['status' => 'closed', 'end_time' => now()]);

        // Reset previously live artists to closed
        Artist::where('status', 'live')->update(['status' => 'closed']);

        $artist = Artist::findOrFail($request->artist_id);
        $artist->update(['status' => 'live']);

        // Create new performance record without voting window
        $performance = Performance::create([
            'artist_id' => $artist->id,
            'event_id' => $event->id,
            'status' => 'live',
            'start_time' => now(),
            'voting_started_at' => null,
            'voting_ends_at' => null,
            'is_voting_paused' => false,
        ]);

        return back()->with('success', "{$artist->name} is now ON STAGE!");
    }

    public function openVoting(Performance $performance)
    {
        $performance->update([
            'voting_started_at' => now(),
            'voting_ends_at' => now()->addMinutes(3), // Default 3 mins
        ]);

        return back()->with('success', 'Voting is now OPEN!');
    }

    public function togglePause(Performance $performance)
    {
        $performance->update([
            'is_voting_paused' => !$performance->is_voting_paused
        ]);

        return back()->with('success', $performance->is_voting_paused ? 'Voting PAUSED' : 'Voting RESUMED');
    }

    public function adjustTime(Request $request, Performance $performance)
    {
        $request->validate([
            'seconds' => 'required|integer',
        ]);

        $performance->update([
            'voting_ends_at' => $performance->voting_ends_at->addSeconds($request->seconds)
        ]);

        $msg = $request->seconds > 0 ? "Added " . abs($request->seconds) . "s" : "Reduced " . abs($request->seconds) . "s";
        return back()->with('success', "Time adjusted: {$msg}");
    }

    public function end(Performance $performance)
    {
        $performance->update([
            'status' => 'closed',
            'end_time' => now()
        ]);

        $performance->artist->update(['status' => 'closed']);

        return back()->with('success', 'Performance CLOSED');
    }

    public function reset(Performance $performance)
    {
        $performance->artist->update(['status' => 'upcoming']);

        $performance->votes()->delete();
        $performance->delete();

        return back()->with('success', 'Performance RESET. Artist can go live again.');
    }
}
