<?php

namespace App\Services\Accounts;

use App\Models\FeedEvent;
use App\Models\Item;
use App\Services\WiseOldMan\WomMetrics;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * The item a feed event is about (a loot drop's most valuable slot, a
 * collection log slot) and the icon the Adventurer's Log shows for it. Shared
 * by the Log and the event map so both name and picture an event alike.
 */
class FeedEventItems
{
    /**
     * Catalogue entries for the events' loot drops (by id) and collection log
     * slots (by name), in one Mongo query each.
     *
     * @param  EloquentCollection<int, FeedEvent>  $events
     * @return array{ids: array<int, array<string, mixed>>, names: array<string, array{id: int, name: string, icon: ?string}>}
     */
    public function lookup(EloquentCollection $events): array
    {
        $ids = $events->where('type', FeedEvent::TYPE_LOOT_DROP)
            ->flatMap(fn (FeedEvent $event): array => array_map(fn ($slot): int => (int) ($slot['id'] ?? 0), self::lootSlots($event)))
            ->unique()
            ->values()
            ->all();

        $names = $events->where('type', FeedEvent::TYPE_COLLECTION_LOG)
            ->map(fn (FeedEvent $event): string => is_string($event->payload['item'] ?? null) ? $event->payload['item'] : '')
            ->filter()
            ->unique()
            ->values()
            ->all();

        return ['ids' => Item::lookupByOsrsIds($ids), 'names' => Item::lookupByNames($names)];
    }

    /**
     * The item an event is about: a loot drop's most valuable slot (by high
     * alchemy value × quantity, else the first), or a collection log slot.
     *
     * @param  array{ids: array<int, array<string, mixed>>, names: array<string, array{id: int, name: string, icon: ?string}>}  $items
     * @return array{id: int, name: string, icon: ?string}|null
     */
    public function item(FeedEvent $event, array $items): ?array
    {
        if ($event->type === FeedEvent::TYPE_COLLECTION_LOG) {
            $name = $event->payload['item'] ?? null;
            $item = is_string($name) ? ($items['names'][$name] ?? null) : null;

            return $item !== null ? ['id' => $item['id'], 'name' => $item['name'], 'icon' => $item['icon']] : null;
        }

        if ($event->type !== FeedEvent::TYPE_LOOT_DROP) {
            return null;
        }

        $top = collect(self::lootSlots($event))
            ->map(fn ($slot): ?array => isset($items['ids'][(int) ($slot['id'] ?? 0)])
                ? [...$items['ids'][(int) $slot['id']], 'worth' => ($items['ids'][(int) $slot['id']]['highalch'] ?? 0) * (int) ($slot['quantity'] ?? 1)]
                : null)
            ->filter()
            ->sortByDesc('worth')
            ->first();

        return $top !== null && $top['name'] !== '' ? ['id' => (int) $top['_id'], 'name' => $top['name'], 'icon' => $top['icon'] ?? null] : null;
    }

    /**
     * @param  array{id: int, name: string, icon: ?string}|null  $item
     * @return array{kind: string, slug: string}|array{src: string}|null
     */
    public function icon(FeedEvent $event, ?array $item): ?array
    {
        return $this->spriteIcon($event)
            ?? (filled($item['icon'] ?? null) ? ['src' => self::iconSrc($item['icon'])] : null);
    }

    /**
     * An event's own picture (skill, quest, combat task), null for events
     * pictured by their item or not at all.
     *
     * @return array{kind: string, slug: string}|array{src: string}|null
     */
    public function spriteIcon(FeedEvent $event): ?array
    {
        return match ($event->type) {
            FeedEvent::TYPE_LEVEL_UP => is_string($event->payload['skill'] ?? null)
                ? WomMetrics::icon(AdventurersLogCopy::skillMetric($event->payload['skill']))
                : null,
            FeedEvent::TYPE_QUEST_COMPLETE => ['kind' => 'quests_tab', 'slug' => 'blue_quests_icon'],
            FeedEvent::TYPE_COMBAT_ACHIEVEMENT => ['kind' => 'quests_tab', 'slug' => 'combat_achievements_icon'],
            default => null,
        };
    }

    /** A catalogue icon (base64 JPEG) as an image source. */
    public static function iconSrc(string $icon): string
    {
        return 'data:image/jpeg;base64,'.$icon;
    }

    /**
     * A loot drop's item slots, tolerating a malformed payload.
     *
     * @return list<array<string, mixed>>
     */
    private static function lootSlots(FeedEvent $event): array
    {
        $items = is_array($event->payload) ? ($event->payload['items'] ?? []) : [];

        return is_array($items) ? array_values(array_filter($items, 'is_array')) : [];
    }
}
