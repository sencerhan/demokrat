<?php

namespace App\Http\Resources;

use App\Models\Talep;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Talep
 */
class TalepResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'body' => $this->body,
            'status' => $this->status->value,
            'author' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ],
            'upvotes_count' => $this->upvotes_count,
            'downvotes_count' => $this->downvotes_count,
            'total_votes_count' => $this->total_votes_count,
            'score' => $this->score,
            'created_at' => $this->created_at?->toISOString(),
            'my_vote' => $this->when(
                $user !== null,
                fn () => $this->votes
                    ->firstWhere('user_id', $user?->id)?->direction->value
            ),
            'can' => [
                'update' => $user?->can('update', $this->resource) ?? false,
                'update_status' => $user?->can('updateStatus', $this->resource) ?? false,
                'delete' => $user?->can('delete', $this->resource) ?? false,
            ],
        ];
    }
}
