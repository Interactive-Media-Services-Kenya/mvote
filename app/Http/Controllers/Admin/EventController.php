<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Event;
use App\Models\VotingQuestion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventController extends Controller
{
    public function index()
    {
        $event = Event::with('questions')->latest()->first();

        return Inertia::render('Admin/EventSetup', [
            'event' => $event,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'questions' => 'array',
            'questions.*.id' => 'nullable|integer',
            'questions.*.question_text' => 'required|string',
            'questions.*.type' => 'required|in:rating,text',
            'questions.*.low_label' => 'nullable|string',
            'questions.*.high_label' => 'nullable|string',
            'questions.*.target' => 'required|in:fan,judge,both',
        ]);

        // Default company_id if not present (assuming 1 for now)
        $eventData = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'is_active' => $request->has('is_active') ? $validated['is_active'] : true,
            'company_id' => 1,
        ];

        // If no ID provided, try to find the latest event so we don't duplicate
        $eventId = $request->id;
        if (!$eventId) {
            $latestEvent = Event::latest()->first();
            $eventId = $latestEvent?->id;
        }

        $event = Event::updateOrCreate(
            ['id' => $eventId],
            $eventData
        );

        // Sync questions
        $syncedIds = [];
        foreach ($validated['questions'] as $index => $qData) {
            $question = $event->questions()->updateOrCreate(
                ['id' => $qData['id'] ?? null],
                [
                    'question_text' => $qData['question_text'],
                    'type' => $qData['type'],
                    'low_label' => $qData['low_label'] ?? null,
                    'high_label' => $qData['high_label'] ?? null,
                    'target' => $qData['target'] ?? 'both',
                    'order' => $index,
                ]
            );
            $syncedIds[] = $question->id;
        }

        // Questions not in the payload should be removed if they are already in the DB
        $event->questions()->whereNotIn('id', $syncedIds)->delete();

        return back()->with('success', 'Event configuration updated successfully.');
    }
}
