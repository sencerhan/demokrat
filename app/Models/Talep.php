<?php

namespace App\Models;

use App\Enums\TalepStatus;
use Database\Factories\TalepFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property int $id
 * @property int $user_id
 * @property string $body
 * @property TalepStatus $status
 * @property int $upvotes_count
 * @property int $downvotes_count
 * @property int $total_votes_count
 * @property int $score
 * @property int $random_key
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['body'])]
class Talep extends Model
{
    /** @use HasFactory<TalepFactory> */
    use HasFactory, LogsActivity;

    protected $table = 'talepler';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TalepStatus::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('talep')
            ->logOnly(['body', 'status'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<TalepVote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(TalepVote::class);
    }
}
