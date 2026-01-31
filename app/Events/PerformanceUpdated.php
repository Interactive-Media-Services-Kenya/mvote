<?php

namespace App\Events;

use App\Models\Performance;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PerformanceUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The message to display in toast notifications.
     */
    public $message;

    /**
     * The structured data representing the current state.
     */
    public $performanceData;

    /**
     * Create a new event instance.
     * * @param Performance|null $performance
     * @param string $message
     */
    public function __construct($performance = null, $message = 'Performance Updated')
    {
        $this->message = $message;

        if ($performance) {
            // Ensure we have the latest database values (timer, pauses, etc.)
            $performance->refresh();

            $this->performanceData = [
                'id' => $performance->id,
                'status' => $performance->status,
                'artist_id' => $performance->artist_id,
                'artist_name' => $performance->artist->name ?? 'Unknown Artist',
                'voting_started_at' => $performance->voting_started_at ? $performance->voting_started_at->toISOString() : null,
                'voting_ends_at' => $performance->voting_ends_at ? $performance->voting_ends_at->toISOString() : null,
                'is_voting_paused' => (bool) $performance->is_voting_paused,
                // Using 0.0 as fallback if no votes exist yet
                'avg_score' => number_format($performance->average_score ?? 0, 1),
                'vote_count' => $performance->votes()->distinct('user_id')->count('user_id'),
            ];
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Must match window.Echo.channel('performances') in Vue
        return [
            new Channel('performances'),
        ];
    }

    /**
     * The event name seen by the frontend.
     * Using this allows you to listen via .performance.updated
     */
    public function broadcastAs(): string
    {
        return 'performance.updated';
    }
}
