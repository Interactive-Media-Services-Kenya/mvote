<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\VotingQuestion;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'questions' => 'array',
            'questions.*.id' => 'nullable|integer',
            'questions.*.question_text' => 'required|string',
            'questions.*.type' => 'required|in:rating,text',
            'questions.*.target' => 'required|in:fan,judge,both',
        ]);

        $event = Event::findOrFail($validated['event_id']);

        // Sync logic
        foreach ($validated['questions'] as $index => $qData) {
            $event->questions()->updateOrCreate(
                ['id' => $qData['id'] ?? null],
                array_merge($qData, ['order' => $index])
            );
        }

        return back()->with('success', 'Metrics synced successfully.');
    }

    public function destroy(VotingQuestion $question)
    {
        $question->delete();
        return back()->with('success', 'Metric removed.');
    }
}
