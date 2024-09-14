<?php

namespace App\Http\Resources;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $bank = [];
        foreach ($this->bank as $tabIndex => $tabs) {
            $tabIndex = $tabIndex + 1;
            $bank["tab-$tabIndex"] = $tabs;

            foreach ($tabs as $itemIndex => $bankItem) {
                $itemId = $bankItem[0];
                $quantity = $bankItem[1];

                $dbItem = Item::where('id', (string)$itemId)->first();

                // Query to get the item with the highest stacked value. This is to get the correct item icon.
                // TODO Need to get item name from plugin
//                $dbItem = Item::where('name', 'Coins')
//                    ->where(function($query) {
//                        $query->where('stacked', '<=', 1500000)
//                              ->orWhereNull('stacked');
//                    })
//                    ->orderBy('stacked', 'desc') // Sort descending, nulls will automatically go to the bottom
//                    ->get();

                if ($dbItem) {
                    $item = (new ItemResource($dbItem))->resolve();

                    $item = [
                        'id' => $item['id'],
                        'name' => $item['name'],
//                        'cost' => $item['cost'],
                        'lowalch' => $item['lowalch'],
                        'highalch' => $item['highalch'],
                        'examine' => $item['examine'],
                        'icon' => $item['icon'],
                    ];
                } else {
                    // If the item is not found, we'll just use a dummy item
                    $item = [
                        'id' => 0,
                        'name' => 'Dwarf remains',
//                        'cost' => 0,
                        'lowalch' => 0,
                        'highalch' => 0,
                        'examine' => 'The body of a Dwarf savaged by Goblins.',
                        'icon' => 'iVBORw0KGgoAAAANSUhEUgAAACQAAAAgCAYAAAB6kdqOAAACPElEQVR4Xs2XS0tCQRTHO5',
                    ];
                }

                $bank["tab-$tabIndex"][$itemIndex] = [
                    'item' => $item,
                    'quantity' => $quantity,
                ];
            }
        }

        return [
            'id' => $this->_id,
            'account_id' => $this->account_id,
            'bank' => $bank,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
