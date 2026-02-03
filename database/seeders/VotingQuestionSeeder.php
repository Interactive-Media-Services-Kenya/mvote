<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\VotingQuestion;
use Illuminate\Database\Seeder;

class VotingQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $event = Event::first() ?? Event::create([
            'name' => 'Default Voting Event',
            'company_id' => 1,
            'description' => 'Automatically created event for voting questions',
            'is_active' => true,
        ]);

        $questions = [
            // Fan Questions
            [
                'question_text' => 'Overall performance - Did the artists feel like the represent the Kenyan sound?',
                'low_label' => 'Meeeh',
                'high_label' => 'Ameweza Mbaya!!',
                'target' => 'fan',
                'type' => 'rating',
            ],
            [
                'question_text' => 'Crowd Connection: Did the artist connect with you and the crowd during their performance?',
                'low_label' => 'Ata sikunotice',
                'high_label' => 'Moto kama pasi',
                'target' => 'fan',
                'type' => 'rating',
            ],
            [
                'question_text' => 'Replay Value: Would you go to this artists show or check them out on Spotify and YouTube in your...',
                'low_label' => 'Imagine Zii',
                'high_label' => 'Twende Tukienjoy',
                'target' => 'fan',
                'type' => 'rating',
            ],
            [
                'question_text' => 'Overall: How would you rate the artist based on their performance',
                'low_label' => 'They were okay',
                'high_label' => 'I felt it 100%!!!',
                'target' => 'fan',
                'type' => 'rating',
            ],

            // Judge Questions
            [
                'question_text' => "Performance: How would you rate the artist's live performance quality?",
                'low_label' => 'Needs Development',
                'high_label' => 'Exceptional',
                'target' => 'judge',
                'type' => 'rating',
            ],
            [
                'question_text' => 'Songwriting & Musical Prowess: Vocals. Cadence, Flow etc',
                'low_label' => 'Weak',
                'high_label' => 'Commanding',
                'target' => 'judge',
                'type' => 'rating',
            ],
            [
                'question_text' => 'Audience Engagement: How effectively did the artist engage the audie...',
                'low_label' => 'Needs Development',
                'high_label' => 'Exceptional',
                'target' => 'judge',
                'type' => 'rating',
            ],
            [
                'question_text' => 'Star Quality: Based on what you saw, does this artist have growth potentia...',
                'low_label' => "Didn't feel it",
                'high_label' => 'They are a STAR!',
                'target' => 'judge',
                'type' => 'rating',
            ],
            [
                'question_text' => 'Industry Readiness: Would you consider working with or booking this a...',
                'low_label' => 'Not at this time',
                'high_label' => 'Definitely Yes',
                'target' => 'judge',
                'type' => 'rating',
            ],
            [
                'question_text' => 'Overall, how would you rate the artist based on their performance?',
                'low_label' => 'Okay',
                'high_label' => 'Amazing!',
                'target' => 'judge',
                'type' => 'rating',
            ],
            [
                'question_text' => 'Any brief notes or recommendations about this artist?',
                'low_label' => null,
                'high_label' => null,
                'target' => 'judge',
                'type' => 'text',
            ],
        ];

        foreach ($questions as $index => $q) {
            VotingQuestion::updateOrCreate(
                [
                    'event_id' => $event->id,
                    'question_text' => $q['question_text']
                ],
                [
                    'type' => $q['type'],
                    'low_label' => $q['low_label'],
                    'high_label' => $q['high_label'],
                    'target' => $q['target'],
                    'order' => $index,
                ]
            );
        }
    }
}
