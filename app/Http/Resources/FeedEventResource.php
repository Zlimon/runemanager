<?php

namespace App\Http\Resources;

use App\Models\FeedEvent;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Plain wire shape for SPEC §8 feed events. Account is included slim — the
 * UI links back to the account page, so we just need username + type for
 * the ironman badge. Loot-drop items are hydrated with their name + icon so
 * the feed can name the drop.
 */
class FeedEventResource extends JsonResource
{
    /**
     * @param  array<int, array<string, mixed>>  $itemsById  Item rows keyed by osrs id (batch hydration).
     */
    public function __construct($resource, public array $itemsById = [])
    {
        parent::__construct($resource);
    }

    /**
     * Hydrate loot-item names/icons across a whole feed page in one Mongo query.
     */
    public static function collectionWith(iterable $events): ResourceCollection
    {
        $itemIds = [];
        foreach ($events as $event) {
            if ($event->type === FeedEvent::TYPE_LOOT_DROP) {
                foreach ($event->payload['items'] ?? [] as $item) {
                    $itemIds[] = (int) ($item['id'] ?? 0);
                }
            }
        }

        $itemsById = Item::lookupByOsrsIds(array_values(array_unique($itemIds)));

        return self::collection(
            collect($events)->map(fn ($event) => new self($event, $itemsById)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'payload' => $this->resolvePayload(),
            'occurred_at' => $this->occurred_at?->toIso8601String(),
            'account' => [
                'username' => $this->account?->username,
                'account_type' => $this->account?->account_type?->value
                    ?? $this->account?->account_type,
            ],
        ];
    }

    /**
     * Enrich loot-drop items with name + icon; other payloads pass through.
     *
     * @return array<string, mixed>
     */
    private function resolvePayload(): array
    {
        $payload = $this->payload;

        if ($this->type !== FeedEvent::TYPE_LOOT_DROP || empty($payload['items'])) {
            return $payload;
        }

        // Batch map when provided (feed page), else look up this event's items
        // directly (single broadcast).
        $itemsById = $this->itemsById
            ?: Item::lookupByOsrsIds(array_column($payload['items'], 'id'));

        $payload['items'] = array_map(function (array $slot) use ($itemsById): array {
            $details = $itemsById[(int) $slot['id']] ?? null;

            return [
                'id' => (int) $slot['id'],
                'quantity' => (int) $slot['quantity'],
                'name' => $details['name'] ?? null,
                'icon' => $details['icon'] ?? null,
            ];
        }, $payload['items']);

        return $payload;
    }
}
