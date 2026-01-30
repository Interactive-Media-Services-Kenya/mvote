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
            'event_id' => 'required|exists:events,id',
            'questions' => 'array',
            'questions.*.id' => 'nullable|integer',
            'questions.*.question_text' => 'required|string',
            'questions.*.type' => 'required|in:rating,text',
            'questions.*.low_label' => 'nullable|string',
            'questions.*.high_label' => 'nullable|string',
            'questions.*.target' => 'required|in:fan,judge,both',
        ]);

        $event = Event::findOrFail($validated['event_id']);

        // Sync logic
        foreach ($validated['questions'] as $index => $qData) {
            $event->questions()->updateOrCreate(
                ['id' => $qData['id'] ?? null],
                [
                    'question_text' => $qData['question_text'],
                    'type' => $qData['type'],
                    'low_label' => $qData['low_label'] ?? null,
                    'high_label' => $qData['high_label'] ?? null,
                    'target' => $qData['target'],
                    'order' => $index
                ]
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
