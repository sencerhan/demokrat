<?php

namespace App\Events;

use App\Models\Talep;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TalepCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Talep $talep) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('talepler')];
    }

    public function broadcastAs(): string
    {
        return 'talep.created';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $this->talep->loadMissing('user');

        return [
            'id' => $this->talep->id,
            'body' => $this->talep->body,
            'status' => $this->talep->status->value,
            'author' => [
                'id' => $this->talep->user?->id,
                'name' => $this->talep->user?->name,
            ],
            'upvotes_count' => $this->talep->upvotes_count,
            'downvotes_count' => $this->talep->downvotes_count,
            'total_votes_count' => $this->talep->total_votes_count,
            'score' => $this->talep->score,
            'created_at' => $this->talep->created_at?->toISOString(),
        ];
    }
}
