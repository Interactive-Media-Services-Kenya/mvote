<?php

namespace App\Http\Controllers;

use App\Models\Performance;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;

class VotingScoreController extends Controller
{
    public function get_performance_stats($performanceId, $eventId = 1)
    {
        // 1. Define the subquery (the "x" in your SQL)
        $subquery = DB::table('votes')
            ->join('performances', 'performances.id', '=', 'votes.performance_id')
            ->select(
                'performance_id',
                DB::raw('SUM(rating) as rating'),
                DB::raw('COUNT(rating) / 4 as voters'),
                // Nested subquery for the 'total' across the whole event
                DB::raw("(SELECT SUM(rating)
                      FROM votes
                      JOIN performances ON performances.id = votes.performance_id
                      WHERE event_id = ?) as total", [$eventId])
            )
            ->where('event_id', $eventId)
            ->groupBy('performance_id');

        // 2. Wrap it and filter by the specific performance_id
        return DB::table(DB::raw("({$subquery->toSql()}) as x"))
            ->mergeBindings($subquery) // Important: merges the ? bindings for the event_id
            ->where('performance_id', $performanceId)
            ->first();
    }

    public function calculate_score()
    {
        $event = \App\Models\Event::where('is_active', true)->latest()->first();
        if (!$event)
            return response()->json(['error' => 'No active event']);

        $performance = Performance::latest()->first();
        // $rankings = $performance ? $this->rankingService->getEventRankings($performance->id) : collect();

        return app(\App\Services\RankingService::class)->getEventRankings($performance->id);
    }
}
