<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TalepVoteCast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $talepId,
        public int $upvotesCount,
        public int $downvotesCount,
        public int $totalVotesCount,
        public int $score,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('talepler')];
    }

    public function broadcastAs(): string
    {
        return 'talep.vote-cast';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->talepId,
            'upvotes_count' => $this->upvotesCount,
            'downvotes_count' => $this->downvotesCount,
            'total_votes_count' => $this->totalVotesCount,
            'score' => $this->score,
        ];
    }
}
