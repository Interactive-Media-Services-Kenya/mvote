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
            'is_active' => $validated['is_active'] ?? true,
            'company_id' => 1,
        ];

        $event = Event::updateOrCreate(
            ['id' => $request->id],
            $eventData
        );

        // Sync questions
        $existingQuestionIds = collect($validated['questions'])->pluck('id')->filter()->toArray();
        $event->questions()->whereNotIn('id', $existingQuestionIds)->delete();

        foreach ($validated['questions'] as $index => $qData) {
            $event->questions()->updateOrCreate(
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
        }

        return back()->with('success', 'Event configuration updated successfully.');
    }
}
