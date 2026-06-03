<?php

namespace App\Http\Resources;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LootingBagResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $itemIds = array_map(fn ($slot) => (int) $slot[0], $this->looting_bag);
        $items = Item::lookupByOsrsIds($itemIds);

        $lootingBag = array_map(fn ($slot) => [
            'id' => (int) $slot[0],
            'item' => $items[(int) $slot[0]] ?? null,
            'quantity' => (int) $slot[1],
        ], $this->looting_bag);

        return [
            '_id' => $this->_id,
            'account_id' => $this->account_id,
            'items' => $lootingBag,
        ];
    }
}
