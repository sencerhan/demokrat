<?php

namespace App\Services;

use App\Enums\VoteDirection;
use App\Events\TalepVoteCast;
use App\Models\Talep;
use App\Models\TalepVote;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class VoteService
{
    public function castVote(User $voter, Talep $talep, VoteDirection $direction): VoteResult
    {
        [$result, $fresh] = DB::transaction(function () use ($voter, $talep, $direction) {
            /** @var Talep $locked */
            $locked = Talep::whereKey($talep->id)->lockForUpdate()->firstOrFail();

            $existing = TalepVote::query()
                ->where('user_id', $voter->id)
                ->where('talep_id', $locked->id)
                ->first();

            if ($existing === null) {
                TalepVote::create([
                    'user_id' => $voter->id,
                    'talep_id' => $locked->id,
                    'direction' => $direction,
                ]);

                $this->applyDelta($locked, $direction, 1);
                $result = VoteResult::Cast;
            } elseif ($existing->direction === $direction) {
                $existing->delete();

                $this->applyDelta($locked, $direction, -1);
                $result = VoteResult::ToggledOff;
            } else {
                $existing->direction = $direction;
                $existing->save();

                $this->applyDelta($locked, $existing->direction, 1);
                $this->applyDelta($locked, $this->opposite($direction), -1);
                // total_votes_count unchanged on a switch.
                $result = VoteResult::Switched;
            }

            $locked->total_votes_count = $locked->upvotes_count + $locked->downvotes_count;
            $locked->score = $locked->upvotes_count - $locked->downvotes_count;
            $locked->save();

            return [$result, $locked];
        });

        event(new TalepVoteCast(
            $fresh->id,
            $fresh->upvotes_count,
            $fresh->downvotes_count,
            $fresh->total_votes_count,
            $fresh->score,
        ));

        return $result;
    }

    private function applyDelta(Talep $talep, VoteDirection $direction, int $delta): void
    {
        if ($direction === VoteDirection::Up) {
            $talep->upvotes_count += $delta;
        } else {
            $talep->downvotes_count += $delta;
        }
    }

    private function opposite(VoteDirection $direction): VoteDirection
    {
        return $direction === VoteDirection::Up ? VoteDirection::Down : VoteDirection::Up;
    }
}
