<?php

namespace App\Listeners;

use App\Events\VoteSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessVoteSubmission implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(VoteSubmitted $event): void
    {
        $user = $event->user;
        $performance = $event->performance;
        $ratings = $event->ratings;
        $comment = $event->comment;

        $role = $user->role->name ?? 'fan';
        $target = in_array($role, ['judge', 'admin']) ? 'judge' : 'fan';

        foreach ($ratings as $questionId => $answer) {
            $question = \App\Models\VotingQuestion::find($questionId);
            
            if (!$question) continue;

            // Security: Ensure the question is targeted for the user's role (or both)
            if (!in_array($question->target, [$target, 'both'])) continue;

            // Security: Fans are only allowed to submit 'rating' type questions
            if ($role === 'fan' && $question->type !== 'rating') continue;
            
            $rating = null;
            $voteComment = $comment;

            if ($question->type === 'rating') {
                $rating = (int) $answer;
            } else {
                // If text question, the answer is the comment itself
                $voteComment = $answer . ($comment ? "\n---\n" . $comment : "");
            }

            \App\Models\Vote::create([
                'user_id' => $user->id,
                'performance_id' => $performance->id,
                'question_id' => $questionId,
                'rating' => $rating,
                'comment' => $voteComment
            ]);
        }

        \App\Events\PerformanceUpdated::dispatch($performance, 'New vote cast');
    }
}
