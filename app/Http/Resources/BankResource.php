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
        $allItemIds = collect($this->bank)
            ->flatten(1) // Flatten to get all item arrays
            ->pluck(0)   // Extract only the first element (item ID) of each array
            ->unique()   // Ensure IDs are unique
            ->toArray();

        // Fetch all required items in a single query
        $items = Item::select('_id', 'name', 'lowalch', 'highalch', 'examine', 'icon')
            ->whereIn('_id', $allItemIds)
            ->get()
            ->keyBy('_id');

        foreach ($this->bank as $tabIndex => $tabs) {
            $tabIndex = $tabIndex + 1;
            $bank["tab-$tabIndex"] = [];

            foreach ($tabs as $itemIndex => $bankItem) {
                $itemId = $bankItem[0];
                $quantity = $bankItem[1];

                // Lookup the item in the fetched items or use a dummy item
                $dbItem = $items->get($itemId);
                $item = $dbItem ? [
                    'id' => $dbItem->_id,
                    'name' => $dbItem->name,
                    'lowalch' => $dbItem->lowalch,
                    'highalch' => $dbItem->highalch,
                    'examine' => $dbItem->examine,
                    'icon' => $dbItem->icon,
                ] : [
                    'id' => 0,
                    'name' => 'Dwarf remains',
                    'lowalch' => 0,
                    'highalch' => 0,
                    'examine' => 'The body of a Dwarf savaged by Goblins.',
                    'icon' => 'iVBORw0KGgoAAAANSUhEUgAAACQAAAAgCAYAAAB6kdqOAAACPElEQVR4Xs2XS0tCQRTHO5',
                ];

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
