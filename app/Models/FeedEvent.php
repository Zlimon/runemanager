<?php

namespace App\Models;

use App\Support\Instance;
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
 *
 * `milestone` and `value` are derived from the type and payload on every save
 * ({@see self::classify()}) so the Adventurer's Log can select its events in
 * portable SQL instead of comparing jsonb fields.
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

    /** Event types that always belong in the Adventurer's Log. */
    public const MILESTONE_TYPES = [
        self::TYPE_QUEST_COMPLETE,
        self::TYPE_COMBAT_ACHIEVEMENT,
        self::TYPE_COLLECTION_LOG,
        self::TYPE_PET,
    ];

    protected static function booted(): void
    {
        static::saving(function (FeedEvent $event): void {
            $event->forceFill(self::classify((string) $event->type, $event->payload ?? []));
        });
    }

    /**
     * Whether an event is an Adventurer's Log milestone on its own (level 99,
     * every 10th level, quests, combat tasks, collection log slots, pets), and
     * a loot drop's total value. Loot is never a milestone by itself: whether
     * it shows depends on the admin's loot threshold at read time. Pure, so the
     * backfill migration can use it too.
     *
     * @param  array<string, mixed>  $payload
     * @return array{milestone: bool, value: ?int}
     */
    public static function classify(string $type, array $payload): array
    {
        if ($type === self::TYPE_LEVEL_UP) {
            $level = (int) ($payload['level'] ?? 0);

            return ['milestone' => $level === 99 || ($level > 0 && $level % 10 === 0), 'value' => null];
        }

        if ($type === self::TYPE_LOOT_DROP) {
            return ['milestone' => false, 'value' => max(0, (int) ($payload['total_value'] ?? 0))];
        }

        return ['milestone' => in_array($type, self::MILESTONE_TYPES, true), 'value' => null];
    }

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
            'milestone' => 'bool',
            'value' => 'int',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Whether the event belongs in the Adventurer's Log, loot judged against
     * the current admin loot threshold.
     */
    public function isLogMilestone(): bool
    {
        return $this->milestone
            || ($this->type === self::TYPE_LOOT_DROP && (int) $this->value >= Instance::feedLootMinValue());
    }

    /**
     * Events the Adventurer's Log shows: milestones, plus loot at or above the
     * admin loot threshold. The threshold is read per query, so changing it
     * re-selects history.
     */
    #[Scope]
    protected function logMilestones(Builder $query): void
    {
        $minValue = Instance::feedLootMinValue();

        $query->where(fn (Builder $query) => $query
            ->where('milestone', true)
            ->orWhere(fn (Builder $query) => $query
                ->where('type', self::TYPE_LOOT_DROP)
                ->where('value', '>=', $minValue)));
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
