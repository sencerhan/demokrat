<?php

namespace App\Services;

use App\Enums\TalepSortMode;
use App\Models\Talep;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TalepFeedService
{
    public const PER_PAGE = 15;

    /**
     * Fetch a page of the feed for the given sort mode.
     *
     * The returned cursor is an opaque, base64-encoded token. Pass the previous
     * page's cursor back in to fetch the next page. `null` cursor means first page.
     *
     * @return array{items: Collection<int, Talep>, nextCursor: string|null, hasMore: bool}
     */
    public function page(TalepSortMode $mode, ?string $cursor = null, ?int $seed = null, int $perPage = self::PER_PAGE): array
    {
        if ($mode === TalepSortMode::Random) {
            return $this->randomPage($seed ?? $this->randomSeed(), $cursor, $perPage);
        }

        return $this->orderedPage($mode, $cursor, $perPage);
    }

    public function randomSeed(): int
    {
        return random_int(1, 2_000_000_000);
    }

    /**
     * @return array{items: Collection<int, Talep>, nextCursor: string|null, hasMore: bool}
     */
    private function orderedPage(TalepSortMode $mode, ?string $cursor, int $perPage): array
    {
        $decoded = $this->decodeCursor($cursor);

        $query = Talep::query()->with('user');

        [$column, $direction] = match ($mode) {
            TalepSortMode::MostVotes => ['total_votes_count', 'desc'],
            TalepSortMode::MostSupported => ['score', 'desc'],
            TalepSortMode::LeastSupported => ['score', 'asc'],
            default => ['score', 'desc'],
        };

        if ($decoded !== null) {
            $sortValue = $decoded['sort'];
            $lastId = $decoded['id'];

            $query->where(function (Builder $q) use ($column, $direction, $sortValue, $lastId): void {
                if ($direction === 'desc') {
                    $q->where($column, '<', $sortValue)
                        ->orWhere(fn (Builder $inner) => $inner->where($column, $sortValue)->where('id', '>', $lastId));
                } else {
                    $q->where($column, '>', $sortValue)
                        ->orWhere(fn (Builder $inner) => $inner->where($column, $sortValue)->where('id', '>', $lastId));
                }
            });
        }

        $items = $query
            ->orderBy($column, $direction)
            ->orderBy('id')
            ->limit($perPage + 1)
            ->get();

        $hasMore = $items->count() > $perPage;
        $items = $items->take($perPage);

        $nextCursor = null;
        if ($hasMore && $items->isNotEmpty()) {
            /** @var Talep $last */
            $last = $items->last();
            $nextCursor = $this->encodeCursor(['sort' => $last->{$column}, 'id' => $last->id]);
        }

        return ['items' => $items->values(), 'nextCursor' => $nextCursor, 'hasMore' => $hasMore];
    }

    /**
     * Stable seeded-random ordering: rows are ordered by a rotation of their
     * permanent `random_key`, starting at `seed`. A single pass never repeats
     * or skips a row for the lifetime of the seed.
     *
     * @return array{items: Collection<int, Talep>, nextCursor: string|null, hasMore: bool}
     */
    private function randomPage(int $seed, ?string $cursor, int $perPage): array
    {
        $decoded = $this->decodeCursor($cursor);

        $query = Talep::query()->with('user');

        // segment 0 => random_key >= seed (the "top" of the rotation), segment 1 => below seed.
        // Seed is bound as a parameter (never interpolated) so the SQL stays a literal string.
        $segmentExpr = '(CASE WHEN random_key >= ? THEN 0 ELSE 1 END)';

        if ($decoded !== null) {
            $segment = $decoded['segment'];
            $randomKey = $decoded['random_key'];
            $lastId = $decoded['id'];

            $query->where(function (Builder $q) use ($seed, $segment, $randomKey, $lastId): void {
                $q->whereRaw('(CASE WHEN random_key >= ? THEN 0 ELSE 1 END) > ?', [$seed, $segment])
                    ->orWhere(fn (Builder $inner) => $inner
                        ->whereRaw('(CASE WHEN random_key >= ? THEN 0 ELSE 1 END) = ?', [$seed, $segment])
                        ->where('random_key', '>', $randomKey))
                    ->orWhere(fn (Builder $inner) => $inner
                        ->whereRaw('(CASE WHEN random_key >= ? THEN 0 ELSE 1 END) = ?', [$seed, $segment])
                        ->where('random_key', $randomKey)
                        ->where('id', '>', $lastId));
            });
        }

        $items = $query
            ->orderByRaw($segmentExpr, [$seed])
            ->orderBy('random_key')
            ->orderBy('id')
            ->limit($perPage + 1)
            ->get();

        $hasMore = $items->count() > $perPage;
        $items = $items->take($perPage);

        $nextCursor = null;
        if ($hasMore && $items->isNotEmpty()) {
            /** @var Talep $last */
            $last = $items->last();
            $nextCursor = $this->encodeCursor([
                'segment' => $last->random_key >= $seed ? 0 : 1,
                'random_key' => $last->random_key,
                'id' => $last->id,
            ]);
        }

        return ['items' => $items->values(), 'nextCursor' => $nextCursor, 'hasMore' => $hasMore];
    }

    /**
     * @param  array<string, int>  $payload
     */
    private function encodeCursor(array $payload): string
    {
        return base64_encode((string) json_encode($payload));
    }

    /**
     * @return array<string, int>|null
     */
    private function decodeCursor(?string $cursor): ?array
    {
        if ($cursor === null || $cursor === '') {
            return null;
        }

        $decoded = json_decode((string) base64_decode($cursor, true), true);

        if (! is_array($decoded)) {
            return null;
        }

        /** @var array<string, int> $decoded */
        return $decoded;
    }
}
