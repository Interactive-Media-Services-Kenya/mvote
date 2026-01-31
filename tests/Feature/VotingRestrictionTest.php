<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Event;
use App\Models\Performance;
use App\Models\Role;
use App\Models\User;
use App\Models\VotingQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VotingRestrictionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_only_vote_once_per_performance()
    {
        $role = Role::create(['name' => 'fan']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $event = Event::create(['name' => 'Test Event', 'is_active' => true]);
        $artist = Artist::create(['name' => 'Test Artist', 'genre_id' => 1, 'is_active' => true]);
        $performance = Performance::create([
            'artist_id' => $artist->id,
            'event_id' => $event->id,
            'status' => 'live',
            'voting_started_at' => now()->subMinute(),
            'voting_ends_at' => now()->addMinutes(5),
        ]);
        $question = VotingQuestion::create([
            'event_id' => $event->id,
            'question_text' => 'How was the stage presence?',
            'type' => 'rating',
            'target' => 'both',
            'order' => 1,
        ]);

        $this->actingAs($user);

        // First vote
        $response = $this->post('/vote', [
            'artist_id' => $artist->id,
            'performance_id' => $performance->id,
            'ratings' => [$question->id => 5],
            'comment' => 'Great!',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseCount('votes', 1);

        // Second vote attempt
        $response = $this->post('/vote', [
            'artist_id' => $artist->id,
            'performance_id' => $performance->id,
            'ratings' => [$question->id => 4],
            'comment' => 'Better!',
        ]);

        $response->assertSessionHas('error', 'You have already voted for this performance.');
        $this->assertDatabaseCount('votes', 1);
    }
}
