<?php

namespace App\Http\Resources;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Serialises a Loot entry with the item rows hydrated from the Mongo Item
 * collection (name + icon for rendering). Use the static ::collectionWith()
 * helper to avoid an N+1 across a list of drops.
 */
class LootResource extends JsonResource
{
    /**
     * @param  array<int, array<string, mixed>>  $itemsById  Item rows keyed by `_id`.
     */
    public function __construct($resource, public array $itemsById = [])
    {
        parent::__construct($resource);
    }

    /**
     * Hydrate item details in a single Mongo query for an entire list.
     */
    public static function collectionWith(iterable $loots): ResourceCollection
    {
        $itemIds = [];
        foreach ($loots as $loot) {
            foreach ($loot->items ?? [] as $item) {
                $itemIds[] = (int) ($item['id'] ?? 0);
            }
        }

        $itemsById = Item::lookupByOsrsIds(array_values(array_unique($itemIds)));

        return self::collection(
            collect($loots)->map(fn ($loot) => new self($loot, $itemsById)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $items = array_map(function (array $slot) {
            $details = $this->itemsById[(int) $slot['id']] ?? null;

            return [
                'id' => (int) $slot['id'],
                'quantity' => (int) $slot['quantity'],
                'name' => $details['name'] ?? null,
                'icon' => $details['icon'] ?? null,
                'examine' => $details['examine'] ?? null,
            ];
        }, $this->items ?? []);

        return [
            'source' => $this->source,
            'items' => $items,
            'total_value' => (int) ($this->total_value ?? 0),
            'killed_at' => $this->killed_at?->toIso8601String(),
        ];
    }
}
