<?php

namespace App\Http\Controllers;

use App\Enums\VoteDirection;
use App\Http\Requests\Talep\StoreTalepVoteRequest;
use App\Models\Talep;
use App\Services\VoteService;
use Illuminate\Http\RedirectResponse;

class TalepVoteController extends Controller
{
    public function store(StoreTalepVoteRequest $request, Talep $talep, VoteService $service): RedirectResponse
    {
        $service->castVote(
            $request->user(),
            $talep,
            $request->enum('direction', VoteDirection::class),
        );

        return back();
    }
}
