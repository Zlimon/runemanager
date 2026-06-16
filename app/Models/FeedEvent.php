<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * SPEC §8 Live Feed — one row per qualifying in-game event surfaced by the
 * push pipeline. Event types are open-ended (kept as strings rather than an
 * enum) so adding a new event source — combat achievement, collection log
 * slot, group bank drop — doesn't require a migration.
 *
 * @property int $id
 * @property int $account_id
 * @property string $type
 * @property array<string, mixed> $payload
 * @property Carbon $occurred_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Account $account
 */
class FeedEvent extends Model
{
    public const TYPE_LEVEL_UP = 'level_up';

    public const TYPE_LOOT_DROP = 'loot_drop';

    public const TYPE_QUEST_COMPLETE = 'quest_complete';

    public const TYPE_COMBAT_ACHIEVEMENT = 'combat_achievement';

    protected $fillable = [
        'account_id',
        'type',
        'payload',
        'occurred_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function scopeRecent(Builder $query, int $limit = 50): Builder
    {
        return $query->orderByDesc('occurred_at')->orderByDesc('id')->limit($limit);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}
