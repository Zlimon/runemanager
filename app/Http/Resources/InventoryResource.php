<?php

namespace App\Http\Resources;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $inventory = array_map(function ($slot) {
            $itemId = $slot[0];
            $quantity = $slot[1];

            if ($itemId > 0) {
                $item = Item::find($itemId);

                $item = $item ? [
                    'id' => $item->id,
                    'name' => $item->name,
                    'lowalch' => $item->lowalch,
                    'highalch' => $item->highalch,
                    'examine' => $item->examine,
                    'icon' => $item->icon,
                ] : [
                    'id' => -1,
                    'name' => null,
                    'lowalch' => null,
                    'highalch' => null,
                    'examine' => null,
                    'icon' => null,
                ];
            }

            return [
                'item' => $item,
                'quantity' => $quantity,
            ];
        }, $this->inventory);

        return [
            'id' => $this->_id,
            'account_id' => $this->account_id,
            'inventory' => $inventory,
        ];
    }
}
