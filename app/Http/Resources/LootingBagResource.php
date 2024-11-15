<?php

namespace App\Http\Resources;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LootingBagResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $itemIds = array_map(function ($slot) {
            return $slot[0];
        }, $this->looting_bag);

        $items = Item::select('_id', 'name', 'lowalch', 'highalch', 'examine', 'icon')->whereIn('_id', $itemIds)->get()->keyBy('_id');

        $getItem = fn($id) => $items->get($id);

        $lootingBag = array_map(function ($slot) use ($getItem) {
            return [
                '_id' => $slot[0],
                'item' => $getItem($slot[0]),
                'amount' => $slot[1],
            ];
        }, $this->looting_bag);

        return [
            '_id' => $this->_id,
            'account_id' => $this->account_id,
            'looting_bag' => $lootingBag,
        ];
    }
}
