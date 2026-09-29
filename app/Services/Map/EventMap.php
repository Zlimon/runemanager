<?php

namespace App\Services\Map;

use App\Models\Account;
use App\Models\FeedEvent;
use App\Models\User;
use App\Services\Accounts\AdventurersLogCopy;
use App\Services\Accounts\FeedEventItems;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * Where accounts' feed events happened, for the Live Map's events layer.
 *
 * Visibility: a viewer always sees their own accounts' events; another
 * member's account shows only once its owner turned on "Show my events on the
 * map" (`accounts.show_events_on_map`). Events at the same tile are grouped
 * into one marker. Titles and icons match the Adventurer's Log.
 */
class EventMap
{
    /** Most events listed in one marker's popup. */
    public const EVENTS_PER_MARKER = 20;

    /** Most accounts one request may ask for. */
    public const MAX_ACCOUNTS = 20;

    public function __construct(private FeedEventItems $feedItems) {}

    /**
     * Whether a viewer may see where an account's events happened, on every
     * surface (the map, the feed, the Adventurer's Log): the owner always,
     * anyone else (guests included) only once the owner opted in. An
     * unclaimed account has no one whose consent could count. The SQL form
     * is {@see self::visible()}.
     */
    public static function positionVisibleTo(?User $viewer, ?Account $account): bool
    {
        if ($account === null || $account->user_id === null) {
            return false;
        }

        return ($viewer !== null && $viewer->id === $account->user_id) || (bool) $account->show_events_on_map;
    }

    /**
     * The newest positioned events of the given accounts (those the viewer
     * may see; other ids are silently dropped), capped at $limit and grouped
     * by tile. Markers are ordered by their newest event.
     *
     * A marker's `count` covers the events inside the returned window only:
     * when `truncated`, older events at the same tile are not counted.
     *
     * Item pictures (loot, collection log) are not repeated per event: an
     * event carries its `item_id`, and `items` maps each id to its image once.
     * `icon` holds the event's own picture (skill, quest, combat task).
     *
     * @param  array<int, mixed>  $accountIds
     * @return array{markers: list<array{x: int, y: int, plane: int, location: ?string, count: int, events: list<array<string, mixed>>}>, items: array<int, string>, truncated: bool}
     */
    public function markers(User $viewer, array $accountIds, ?string $type = null, int $limit = 2000): array
    {
        $limit = max(1, $limit);
        $ids = $this->visible($viewer)->whereIn('id', self::sanitiseIds($accountIds))->pluck('id')->all();

        if ($ids === []) {
            return ['markers' => [], 'items' => [], 'truncated' => false];
        }

        /** @var EloquentCollection<int, FeedEvent> $events */
        $events = FeedEvent::query()
            ->with('account:id,username')
            ->whereIn('account_id', $ids)
            ->positioned()
            ->when($type !== null, fn (Builder $query) => $query->ofType($type))
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit($limit + 1)
            ->get();

        $truncated = $events->count() > $limit;

        // Group first, so only the events a popup lists are titled and have
        // their items looked up.
        $markers = [];
        $listed = new EloquentCollection;

        foreach ($events->take($limit) as $event) {
            $key = $event->world_x.':'.$event->world_y.':'.($event->world_plane ?? 0);

            $markers[$key] ??= [
                'x' => $event->world_x,
                'y' => $event->world_y,
                'plane' => $event->world_plane ?? 0,
                'location' => null,
                'count' => 0,
                'events' => [],
            ];

            $markers[$key]['count']++;
            $markers[$key]['location'] ??= $event->location;

            if (count($markers[$key]['events']) < self::EVENTS_PER_MARKER) {
                $markers[$key]['events'][] = $event;
                $listed->push($event);
            }
        }

        $lookup = $this->feedItems->lookup($listed);
        $items = [];

        foreach ($markers as &$marker) {
            $marker['events'] = array_map(function (FeedEvent $event) use ($lookup, &$items): array {
                $item = $this->feedItems->item($event, $lookup);
                $hasPicture = $item !== null && filled($item['icon']);

                if ($hasPicture) {
                    $items[$item['id']] ??= FeedEventItems::iconSrc($item['icon']);
                }

                return [
                    'id' => $event->id,
                    'account_id' => $event->account_id,
                    'username' => $event->account?->username,
                    'type' => $event->type,
                    'title' => AdventurersLogCopy::forFeedEvent($event, $item['name'] ?? null)['title'],
                    'occurred_at' => $event->occurred_at->toIso8601String(),
                    'icon' => $this->feedItems->spriteIcon($event),
                    'item_id' => $hasPicture ? $item['id'] : null,
                ];
            }, $marker['events']);
        }
        unset($marker);

        return ['markers' => array_values($markers), 'items' => $items, 'truncated' => $truncated];
    }

    /**
     * The accounts the viewer can pick: all of their own (with how many
     * positioned events each has), then other members' opted-in accounts that
     * have at least one positioned event. Only an existence check is run for
     * the others, so the list stays cheap on a large clan; their
     * `event_count` is null.
     *
     * @return list<array{id: int, username: string, account_type: ?string, own: bool, event_count: ?int}>
     */
    public function selectableAccounts(User $viewer): array
    {
        $own = $viewer->account()
            ->withCount(['feedEvents as event_count' => fn (Builder $query) => $query->positioned()])
            ->get();

        $others = Account::query()
            ->where('show_events_on_map', true)
            ->whereNotNull('user_id')
            ->where('user_id', '!=', $viewer->id)
            ->whereHas('feedEvents', fn (Builder $query) => $query->positioned())
            ->get();

        $row = fn (Account $account, bool $own): array => [
            'id' => $account->id,
            'username' => $account->username,
            'account_type' => $account->account_type?->value,
            'own' => $own,
            'event_count' => $own ? (int) $account->event_count : null,
        ];

        return [
            ...$own->sortBy(fn (Account $account): string => mb_strtolower($account->username))->map(fn (Account $account): array => $row($account, true))->values()->all(),
            ...$others->sortBy(fn (Account $account): string => mb_strtolower($account->username))->map(fn (Account $account): array => $row($account, false))->values()->all(),
        ];
    }

    /**
     * The map page's `?events=1,2` preselection: the listed ids the viewer
     * can pick, in order, without duplicates.
     *
     * @param  list<array{id: int}>  $selectable  From {@see self::selectableAccounts()}.
     * @return list<int>
     */
    public function preselection(array $selectable, ?string $events): array
    {
        $allowed = array_column($selectable, 'id');

        return array_values(array_filter(
            self::sanitiseIds(explode(',', (string) $events)),
            fn (int $id): bool => in_array($id, $allowed, true),
        ));
    }

    /**
     * Whole-number ids from untrusted input, deduplicated and capped at
     * MAX_ACCOUNTS; anything else is dropped.
     *
     * @param  array<int, mixed>  $ids
     * @return list<int>
     */
    private static function sanitiseIds(array $ids): array
    {
        $clean = [];

        foreach ($ids as $id) {
            $id = is_string($id) ? trim($id) : $id;

            if (is_int($id) || (is_string($id) && ctype_digit($id))) {
                $clean[(int) $id] = (int) $id;
            }
        }

        return array_slice(array_values($clean), 0, self::MAX_ACCOUNTS);
    }

    /**
     * Accounts whose events the viewer may see: their own, and claimed
     * accounts whose owner opted in. An unclaimed account has no one whose
     * consent could count. The PHP form is {@see self::positionVisibleTo()}.
     *
     * @return Builder<Account>
     */
    private function visible(User $viewer): Builder
    {
        return Account::query()->where(fn (Builder $query) => $query
            ->where('user_id', $viewer->id)
            ->orWhere(fn (Builder $query) => $query->where('show_events_on_map', true)->whereNotNull('user_id')));
    }
}
