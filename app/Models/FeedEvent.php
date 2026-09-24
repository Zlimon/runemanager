<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A Live Feed entry: one row per qualifying in-game event surfaced by the
 * push pipeline. Event types are open-ended (kept as strings rather than an
 * enum) so adding a new event source — combat achievement, collection log
 * slot, group bank drop — doesn't require a migration.
 */
#[Fillable(['account_id', 'type', 'payload', 'screenshot_path', 'pinned_at', 'occurred_at'])]
class FeedEvent extends Model
{
    use HasFactory;

    public const TYPE_LEVEL_UP = 'level_up';

    public const TYPE_LOOT_DROP = 'loot_drop';

    public const TYPE_QUEST_COMPLETE = 'quest_complete';

    public const TYPE_COMBAT_ACHIEVEMENT = 'combat_achievement';

    public const TYPE_COLLECTION_LOG = 'collection_log';

    public const TYPE_PET = 'pet';

    public const TYPE_DEATH = 'death';

    public const TYPE_REWARD = 'reward';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'occurred_at' => 'datetime',
            'pinned_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    #[Scope]
    protected function recent(Builder $query, int $limit = 50): void
    {
        $query->orderByDesc('occurred_at')->orderByDesc('id')->limit($limit);
    }

    #[Scope]
    protected function ofType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }

    /**
     * Pinned events, newest pin first — drives the account achievement gallery.
     */
    #[Scope]
    protected function pinned(Builder $query): void
    {
        $query->whereNotNull('pinned_at')->orderByDesc('pinned_at');
    }
}
