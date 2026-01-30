<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Event;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['id' => 1],
            ['name' => 'Demo Entertainment Group']
        );

        $event = Event::updateOrCreate(
            ['name' => 'Kenya Music Awards 2026'],
            [
                'company_id' => $company->id,
                'description' => 'A premier musical talent competition showcasing the best of Kenyan music.',
                'is_active' => true,
            ]
        );

        $questions = [
            [
                'question_text' => 'How much did you enjoy the energy?',
                'type' => 'rating',
                'low_label' => 'Weak',
                'high_label' => 'Fire!',
                'target' => 'both',
                'order' => 0,
            ],
            [
                'question_text' => 'Vocal Clarity & Technical Skill',
                'type' => 'rating',
                'low_label' => 'Needs Work',
                'high_label' => 'Flawless',
                'target' => 'judge',
                'order' => 1,
            ],
            [
                'question_text' => 'What was your favorite part of the performance?',
                'type' => 'text',
                'target' => 'both',
                'order' => 2,
            ],
        ];

        foreach ($questions as $q) {
            $event->questions()->updateOrCreate(
                ['question_text' => $q['question_text']],
                $q
            );
        }
    }
}
