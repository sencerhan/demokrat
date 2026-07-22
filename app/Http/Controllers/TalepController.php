<?php

namespace App\Http\Controllers;

use App\Enums\TalepSortMode;
use App\Enums\TalepStatus;
use App\Http\Requests\Talep\StoreTalepRequest;
use App\Http\Requests\Talep\UpdateTalepRequest;
use App\Http\Requests\Talep\UpdateTalepStatusRequest;
use App\Http\Resources\TalepResource;
use App\Models\Talep;
use App\Services\TalepFeedService;
use App\Services\TalepService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TalepController extends Controller
{
    public function index(Request $request, TalepFeedService $feed): Response
    {
        $mode = TalepSortMode::tryFrom((string) $request->query('mode')) ?? TalepSortMode::Random;
        $cursor = $request->query('cursor');
        $seed = $request->integer('seed') ?: null;

        if ($mode === TalepSortMode::Random && $seed === null) {
            $seed = $feed->randomSeed();
        }

        $page = $feed->page($mode, is_string($cursor) ? $cursor : null, $seed);

        $items = $page['items'];

        if ($request->user() !== null) {
            $items->load(['votes' => fn ($q) => $q->where('user_id', $request->user()->id)]);
        }

        return Inertia::render('Talepler/Index', [
            'talepler' => TalepResource::collection($items)->resolve($request),
            'sortMode' => $mode->value,
            'seed' => $seed,
            'nextCursor' => $page['nextCursor'],
            'hasMore' => $page['hasMore'],
        ]);
    }

    public function store(StoreTalepRequest $request, TalepService $service): RedirectResponse
    {
        $service->create($request->user(), $request->string('body')->toString());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Talebiniz oluşturuldu.')]);

        return back();
    }

    public function update(UpdateTalepRequest $request, Talep $talep, TalepService $service): RedirectResponse
    {
        $service->updateBody($talep, $request->string('body')->toString());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Talep güncellendi.')]);

        return back();
    }

    public function updateStatus(UpdateTalepStatusRequest $request, Talep $talep, TalepService $service): RedirectResponse
    {
        $service->updateStatus($talep, $request->enum('status', TalepStatus::class));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Talep durumu güncellendi.')]);

        return back();
    }

    public function destroy(Talep $talep, TalepService $service): RedirectResponse
    {
        $this->authorize('delete', $talep);

        $service->delete($talep);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Talep silindi.')]);

        return back();
    }
}
