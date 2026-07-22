<?php

namespace App\Services;

use App\Enums\TalepStatus;
use App\Events\TalepCreated;
use App\Events\TalepStatusUpdated;
use App\Models\Talep;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TalepService
{
    public function create(User $author, string $body): Talep
    {
        $talep = new Talep(['body' => $body]);
        $talep->user_id = $author->id;
        $talep->status = TalepStatus::Pending;
        $talep->random_key = random_int(0, 2_000_000_000);
        $talep->save();

        event(new TalepCreated($talep));

        return $talep;
    }

    public function updateBody(Talep $talep, string $body): Talep
    {
        $talep->body = $body;
        $talep->save();

        return $talep;
    }

    public function updateStatus(Talep $talep, TalepStatus $status): Talep
    {
        $talep->status = $status;
        $talep->save();

        event(new TalepStatusUpdated($talep->id, $status));

        return $talep;
    }

    public function delete(Talep $talep): void
    {
        DB::transaction(function () use ($talep): void {
            $talep->delete();
        });
    }
}
