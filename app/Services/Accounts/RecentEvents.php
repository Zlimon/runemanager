<?php

namespace App\Services\Accounts;

use App\Models\Account;
use App\Models\FeedEvent;
use App\Models\User;
use App\Models\WomAchievement;
use App\Models\WomRecord;
use App\Services\Map\EventMap;
use App\Services\WiseOldMan\WomMetrics;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

/**
 * An account's Adventurer's Log: milestone feed events, Wise Old Man
 * achievements and WOM personal records merged into one newest-first list.
 *
 * Each source is keyset-queried for `limit + 1` rows after the cursor, the
 * three are merged in PHP and cut to `limit`. A 99 that the feed already
 * logged is not repeated as a WOM achievement, and records of one metric set
 * at the same moment collapse into one event ("in a single week, my best yet
 * (also a new monthly best)").
 */
class RecentEvents
{
    /** Record periods the log shows (no day or five-minute bests). */
    public const RECORD_PERIODS = ['week', 'month', 'year'];

    /** Record metrics the log leaves out. */
    public const HIDDEN_RECORD_METRICS = ['ehp', 'ehb'];

    public function __construct(private FeedEventItems $feedItems) {}

    /**
     * Feed rows carry where they happened only when $viewer may see it
     * ({@see EventMap::positionVisibleTo()}); without a viewer, never. Their
     * screenshot follows {@see FeedEvent::screenshotVisibleTo()} the same way.
     *
     * @return array{events: list<RecentEvent>, next_cursor: ?string}
     */
    public function page(Account $account, ?string $before = null, int $limit = 15, ?User $viewer = null): array
    {
        $limit = max(1, $limit);
        $cursor = self::decodeCursor($before);

        $events = collect()
            ->concat($this->feedEvents($account, $cursor, $limit + 1, $viewer))
            ->concat($this->achievements($account, $cursor, $limit + 1))
            ->concat($this->records($account, $cursor, $limit + 1))
            ->sort(RecentEvent::compare(...))
            ->values();

        $page = $events->take($limit)->values();

        return [
            'events' => $page->all(),
            'next_cursor' => $events->count() > $limit ? self::encodeCursor($page->last()) : null,
        ];
    }

    public static function encodeCursor(RecentEvent $event): string
    {
        return rtrim(strtr(base64_encode($event->occurredAt->getTimestampMs().':'.$event->rank().':'.$event->key), '+/', '-_'), '=');
    }

    /**
     * @return array{ms: int, rank: int, key: int}|null Null for a missing or malformed cursor (first page).
     */
    public static function decodeCursor(?string $cursor): ?array
    {
        if ($cursor === null || $cursor === '') {
            return null;
        }

        $decoded = base64_decode(strtr($cursor, '-_', '+/'), true);

        if ($decoded === false || ! preg_match('/^(\d{1,15}):([0-2]):(\d{1,18})$/', $decoded, $match)) {
            return null;
        }

        return ['ms' => (int) $match[1], 'rank' => (int) $match[2], 'key' => (int) $match[3]];
    }

    /**
     * @param  array{ms: int, rank: int, key: int}|null  $cursor
     * @return Collection<int, RecentEvent>
     */
    private function feedEvents(Account $account, ?array $cursor, int $take, ?User $viewer): Collection
    {
        $withPositions = EventMap::positionVisibleTo($viewer, $account);

        $query = FeedEvent::query()
            ->where('account_id', $account->id)
            ->logMilestones()
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit($take);

        $this->afterCursor($query, 'occurred_at', RecentEvent::RANKS[RecentEvent::SOURCE_FEED], $cursor);

        /** @var EloquentCollection<int, FeedEvent> $rows */
        $rows = $query->get();
        $rows->each->setRelation('account', $account);
        $items = $this->feedItems->lookup($rows);
        $managesScreenshots = $rows->isNotEmpty() && $rows->first()->screenshotManagedBy($viewer);

        return $rows->map(function (FeedEvent $event) use ($items, $withPositions, $viewer, $managesScreenshots): RecentEvent {
            $item = $this->feedItems->item($event, $items);
            $copy = AdventurersLogCopy::forFeedEvent($event, $item['name'] ?? null);

            return new RecentEvent(
                source: RecentEvent::SOURCE_FEED,
                key: $event->id,
                type: $event->type,
                title: $copy['title'],
                body: $copy['body'],
                occurredAt: $event->occurred_at->toImmutable(),
                icon: $this->feedItems->icon($event, $item),
                feedEventId: $event->id,
                pinned: $event->pinned_at !== null,
                screenshotUrl: $event->screenshotVisibleTo($viewer) ? route('screenshots.file', $event->id) : null,
                screenshotHidden: $managesScreenshots ? $event->screenshot_path !== null && $event->screenshot_hidden : null,
                position: $withPositions && $event->world_x !== null ? [
                    'x' => $event->world_x,
                    'y' => $event->world_y,
                    'plane' => $event->world_plane ?? 0,
                    'location' => $event->location,
                ] : null,
            );
        })->toBase();
    }

    /**
     * Dated, current achievements, minus the 99s the feed already logged.
     *
     * @param  array{ms: int, rank: int, key: int}|null  $cursor
     * @return Collection<int, RecentEvent>
     */
    private function achievements(Account $account, ?array $cursor, int $take): Collection
    {
        $loggedNinetyNines = $this->loggedNinetyNines($account);

        $query = WomAchievement::query()
            ->where('account_id', $account->id)
            ->whereNotNull('achieved_at')
            ->where('legacy', false)
            ->when($loggedNinetyNines !== [], fn (Builder $query) => $query->whereNot(fn (Builder $query) => $query
                ->where('measure', 'experience')
                ->where('threshold', AdventurersLogCopy::LEVEL_99_EXPERIENCE)
                ->whereIn('metric', $loggedNinetyNines)))
            ->orderByDesc('achieved_at')
            ->orderByDesc('id')
            ->limit($take);

        $this->afterCursor($query, 'achieved_at', RecentEvent::RANKS[RecentEvent::SOURCE_ACHIEVEMENT], $cursor);

        return $query->get()->map(function (WomAchievement $achievement): RecentEvent {
            $copy = AdventurersLogCopy::forAchievement($achievement);

            return new RecentEvent(
                source: RecentEvent::SOURCE_ACHIEVEMENT,
                key: $achievement->id,
                type: 'achievement',
                title: $copy['title'],
                body: $copy['body'],
                occurredAt: $achievement->achieved_at->toImmutable(),
                approximate: AdventurersLogCopy::isApproximate($achievement),
                icon: WomMetrics::icon($achievement->metric),
            );
        })->toBase();
    }

    /**
     * Week/month/year records, one event per metric and moment. A group is
     * keyed by its lowest row id, so the rows of a group always travel together:
     * rows strictly older than the cursor are paged in SQL (then the oldest
     * moment fetched is completed), and the cursor's own moment is read whole
     * and filtered in PHP.
     *
     * @param  array{ms: int, rank: int, key: int}|null  $cursor
     * @return Collection<int, RecentEvent>
     */
    private function records(Account $account, ?array $cursor, int $take): Collection
    {
        $base = fn (): Builder => WomRecord::query()
            ->where('account_id', $account->id)
            ->whereIn('period', self::RECORD_PERIODS)
            ->whereNotIn('metric', self::HIDDEN_RECORD_METRICS);

        $fetch = $take * count(self::RECORD_PERIODS);
        $older = $base()
            ->when($cursor !== null, fn (Builder $query) => $query->where('achieved_at', '<', self::cursorTime($cursor)))
            ->orderByDesc('achieved_at')
            ->orderByDesc('id')
            ->limit($fetch)
            ->get();

        $rows = $older;

        if ($older->count() === $fetch) {
            $rows = $rows->concat($base()->where('achieved_at', $older->last()->achieved_at)->get());
        }

        if ($cursor !== null) {
            $rows = $rows->concat($base()->where('achieved_at', self::cursorTime($cursor))->get());
        }

        $rank = RecentEvent::RANKS[RecentEvent::SOURCE_RECORD];

        return $rows->unique('id')
            ->groupBy(fn (WomRecord $record): string => $record->metric.'|'.$record->achieved_at->getTimestamp())
            ->map(function (Collection $group): RecentEvent {
                /** @var WomRecord $first */
                $first = $group->sortBy('id')->first();
                $copy = AdventurersLogCopy::forRecord($first->metric, $group->pluck('value', 'period')->all());

                return new RecentEvent(
                    source: RecentEvent::SOURCE_RECORD,
                    key: $first->id,
                    type: 'record',
                    title: $copy['title'],
                    body: $copy['body'],
                    occurredAt: $first->achieved_at->toImmutable(),
                    icon: WomMetrics::icon($first->metric),
                );
            })
            ->filter(fn (RecentEvent $event): bool => $cursor === null || $event->isAfter($cursor['ms'], $cursor['rank'], $cursor['key']))
            ->sort(RecentEvent::compare(...))
            ->take($take)
            ->values()
            ->toBase();
    }

    /**
     * Keyset condition for a source ordered by `$column` desc, id desc, placed
     * at `$rank` among the sources.
     *
     * @param  array{ms: int, rank: int, key: int}|null  $cursor
     */
    private function afterCursor(Builder $query, string $column, int $rank, ?array $cursor): void
    {
        if ($cursor === null) {
            return;
        }

        $time = self::cursorTime($cursor);

        $query->where(fn (Builder $query) => $query
            ->where($column, '<', $time)
            ->when($rank > $cursor['rank'], fn (Builder $query) => $query->orWhere($column, $time))
            ->when($rank === $cursor['rank'], fn (Builder $query) => $query->orWhere(fn (Builder $query) => $query
                ->where($column, $time)
                ->where('id', '<', $cursor['key']))));
    }

    /**
     * @param  array{ms: int, rank: int, key: int}  $cursor
     */
    private static function cursorTime(array $cursor): CarbonImmutable
    {
        return CarbonImmutable::createFromTimestampMs($cursor['ms'])->setTimezone(config('app.timezone'));
    }

    /**
     * WOM metrics of the skills whose 99 the feed already logged.
     *
     * @return list<string>
     */
    private function loggedNinetyNines(Account $account): array
    {
        return FeedEvent::query()
            ->where('account_id', $account->id)
            ->where('type', FeedEvent::TYPE_LEVEL_UP)
            ->where('milestone', true)
            ->pluck('payload')
            ->filter(fn ($payload): bool => is_array($payload)
                && (int) ($payload['level'] ?? 0) === 99
                && is_string($payload['skill'] ?? null) && $payload['skill'] !== '')
            ->map(fn (array $payload): string => AdventurersLogCopy::skillMetric((string) $payload['skill']))
            ->unique()
            ->values()
            ->all();
    }
}
