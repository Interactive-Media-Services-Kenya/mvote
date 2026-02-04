<?php

namespace App\Http\Controllers;

use App\Models\Performance;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoteController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'artist_id' => 'required|exists:artists,id',
            'performance_id' => 'required|exists:performances,id',
            'ratings' => 'required|array',
            'comment' => 'nullable|string'
        ]);

        $user = Auth::user();
        $performance = Performance::findOrFail($request->performance_id);

        if ($performance->status !== 'live') {
            return back()->with('error', 'Rating session is closed.');
        }

        $now = now();
        if (!$performance->voting_started_at || $performance->voting_started_at->isFuture()) {
            return back()->with('error', 'Voting has not started yet.');
        }

        if ($performance->voting_ends_at && $performance->voting_ends_at->isPast()) {
            return back()->with('error', 'Voting has expired.');
        }

        if ($performance->is_voting_paused) {
            return back()->with('error', 'Voting is currently paused.');
        }

        // Check if user has already voted for this performance
        $existingVote = Vote::where('user_id', $user->id)
            ->where('performance_id', $performance->id)
            ->exists();

        if ($existingVote) {
            return back()->with('error', 'You have already rated this performance.');
        }

        $role = $user->role->name ?? 'fan';
        $target = in_array($role, ['judge', 'admin']) ? 'judge' : 'fan';

        foreach ($request->ratings as $questionId => $answer) {
            $question = \App\Models\VotingQuestion::find($questionId);
            
            if (!$question) continue;

            // Security: Ensure the question is targeted for the user's role (or both)
            if (!in_array($question->target, [$target, 'both'])) continue;

            // Security: Fans are only allowed to submit 'rating' type questions
            if ($role === 'fan' && $question->type !== 'rating') continue;
            
            $rating = null;
            $voteComment = $request->comment;

            if ($question->type === 'rating') {
                $rating = (int) $answer;
            } else {
                // If text question, the answer is the comment itself
                $voteComment = $answer . ($request->comment ? "\n---\n" . $request->comment : "");
            }

            Vote::create([
                'user_id' => $user->id,
                'performance_id' => $performance->id,
                'question_id' => $questionId,
                'rating' => $rating,
                'comment' => $voteComment
            ]);
        }

        \App\Events\PerformanceUpdated::dispatch($performance, 'New vote cast');

        return back()->with('success', 'Rating submitted successfully!');
    }
}
