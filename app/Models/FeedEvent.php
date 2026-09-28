<?php

namespace App\Models;

use App\Support\Instance;
use App\Support\Roles;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
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
 * portable SQL instead of comparing jsonb fields. The plugin-supplied position
 * (`payload.position`) is promoted the same way ({@see self::position()}) so
 * the event map can select positioned events.
 *
 * A screenshot the plugin attached (`screenshot_path`, on the private disk)
 * can be hidden by the account's owner (`screenshot_hidden`): the event stays
 * in the feed, but only the owner and instance admins see the picture
 * ({@see self::screenshotVisibleTo()}). The path itself is never serialised;
 * viewers only ever get the `screenshots.file` route.
 */
#[Hidden(['screenshot_path'])]
#[Fillable(['account_id', 'type', 'payload', 'screenshot_path', 'screenshot_hidden', 'pinned_at', 'occurred_at'])]
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

    /** Every event type the feed records. */
    public const TYPES = [
        self::TYPE_LEVEL_UP,
        self::TYPE_QUEST_COMPLETE,
        self::TYPE_COMBAT_ACHIEVEMENT,
        self::TYPE_COLLECTION_LOG,
        self::TYPE_PET,
        self::TYPE_DEATH,
        self::TYPE_REWARD,
        self::TYPE_LOOT_DROP,
    ];

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
            $payload = $event->payload ?? [];

            $event->forceFill([
                ...self::classify((string) $event->type, $payload),
                ...self::position($payload),
            ]);
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

    /** Largest world coordinate kept; OSRS tiles sit well inside it, and it fits every column type. */
    public const MAX_COORDINATE = 32767;

    /**
     * The world position columns from a payload's `position` ({x, y, plane,
     * location}), all null when it has none or an unusable one: x and y must
     * be whole numbers in 0..MAX_COORDINATE, and plane (0 when missing) in
     * 0..3. Pure and non-throwing, so the backfill migration can run it over
     * any legacy row.
     *
     * @param  array<string, mixed>  $payload
     * @return array{world_x: ?int, world_y: ?int, world_plane: ?int, location: ?string}
     */
    public static function position(array $payload): array
    {
        $none = ['world_x' => null, 'world_y' => null, 'world_plane' => null, 'location' => null];
        $position = $payload['position'] ?? null;

        if (! is_array($position)) {
            return $none;
        }

        $x = self::wholeNumber($position['x'] ?? null, self::MAX_COORDINATE);
        $y = self::wholeNumber($position['y'] ?? null, self::MAX_COORDINATE);
        $plane = ($position['plane'] ?? null) === null ? 0 : self::wholeNumber($position['plane'], 3);

        if ($x === null || $y === null || $plane === null) {
            return $none;
        }

        $location = $position['location'] ?? null;
        $location = is_scalar($location) && trim((string) $location) !== '' ? mb_substr(trim((string) $location), 0, 80) : null;

        return ['world_x' => $x, 'world_y' => $y, 'world_plane' => $plane, 'location' => $location];
    }

    /** An int (or integer string) in 0..$max, else null. */
    private static function wholeNumber(mixed $value, int $max): ?int
    {
        if (! is_int($value) && ! is_string($value)) {
            return null;
        }

        $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => $max]]);

        return $number === false ? null : $number;
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
            'screenshot_hidden' => 'bool',
            'milestone' => 'bool',
            'value' => 'int',
            'world_x' => 'int',
            'world_y' => 'int',
            'world_plane' => 'int',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Whether a viewer may see this event's screenshot, on every surface (the
     * feed, the Log, the gallery, the file itself): anyone while it is not
     * hidden, and a hidden one only the account's owner and instance admins.
     * False when there is no screenshot.
     */
    public function screenshotVisibleTo(?User $viewer): bool
    {
        if ($this->screenshot_path === null) {
            return false;
        }

        return ! $this->screenshot_hidden || $this->screenshotManagedBy($viewer);
    }

    /**
     * Whether a viewer sees the screenshot's hidden state (and hidden
     * screenshots): the account's owner and instance admins.
     */
    public function screenshotManagedBy(?User $viewer): bool
    {
        if ($viewer === null) {
            return false;
        }

        return ($this->account?->user_id !== null && $viewer->id === $this->account->user_id)
            || $viewer->can(Roles::MANAGE_INSTANCE);
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

    /** Events that carry a world position. */
    #[Scope]
    protected function positioned(Builder $query): void
    {
        $query->whereNotNull('world_x')->whereNotNull('world_y');
    }

    /** Events that carry a screenshot. */
    #[Scope]
    protected function withScreenshot(Builder $query): void
    {
        $query->whereNotNull('screenshot_path');
    }

    /** Events of the user's own accounts. */
    #[Scope]
    protected function ownedBy(Builder $query, User $user): void
    {
        $query->whereIn('account_id', Account::query()->select('id')->where('user_id', $user->id));
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
