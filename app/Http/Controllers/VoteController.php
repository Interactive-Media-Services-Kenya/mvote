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
            return back()->with('error', 'Voting session is closed.');
        }

        foreach ($request->ratings as $questionId => $answer) {
            $question = \App\Models\VotingQuestion::find($questionId);
            
            $rating = null;
            $voteComment = $request->comment;

            if ($question && $question->type === 'rating') {
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

        return back()->with('success', 'Vote submitted successfully!');
    }
}
