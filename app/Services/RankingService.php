<?php

namespace App\Services;

use App\Models\Performance;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class RankingService
{
    /**
     * Calculate bias-adjusted rankings for all performances in an event.
     * Uses Formula: (rating + m * mu) / (voters + m)
     */
    public function getEventRankings($eventId)
    {
        $event = Event::find($eventId);
        if (!$event)
            return collect();

        // 1. Get rating questions count for dynamic "voters" (default to 4 if none found)
        $qCount = DB::table('voting_questions')
            ->where('event_id', $eventId)
            ->whereIn('type', ['rating', 'rate'])
            ->count();
        $qCount = $qCount > 0 ? $qCount : 4;

        // 2. Fetch raw performance stats (per_perf in SQL)
        $perPerf = DB::table('votes')
            ->join('performances', 'votes.performance_id', '=', 'performances.id')
            ->where('performances.event_id', $eventId)
            ->select(
                'votes.performance_id',
                DB::raw('SUM(votes.rating) as rating'),
                DB::raw("COUNT(votes.rating) / $qCount as voters")
            )
            ->groupBy('votes.performance_id')
            ->get();

        if ($perPerf->isEmpty())
            return collect();

        // 3. Calculate Global metrics (mu and total_rating)
        $totalRating = $perPerf->sum('rating');
        $totalVoters = $perPerf->sum('voters');

        if ($totalVoters == 0)
            return collect();

        $m = 10; // Constant bias
        $mu = $totalRating / $totalVoters;

        // 4. Scored & Final (scored in SQL)
        return $perPerf->map(function ($item) use ($m, $mu, $totalRating) {
            $item->total_rating = $totalRating;
            $item->ratio = $totalRating > 0 ? ($item->rating / $totalRating) * 100 : 0;
            $item->bias_rating = ($item->rating + ($m * $mu)) / ($item->voters + $m);
            return $item;
        })->sortByDesc('bias_rating')->values();
    }

    /**
     * Get the bias-adjusted score for a specific performance.
     */
    public function getPerformanceScore(Performance $performance)
    {
        $rankings = $this->getEventRankings($performance->event_id);
        $result = $rankings->firstWhere('performance_id', $performance->id);

        return $result ? round($result->bias_rating, 2) : 0;
    }

    // public function rawSQL(){
    //     $sql =
    // }
}
