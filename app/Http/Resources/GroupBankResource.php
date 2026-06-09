<?php

namespace App\Http\Resources;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupBankResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $entries = $this->items ?? [];

        $itemIds = collect($entries)
            ->pluck(0)
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->all();

        $items = Item::lookupByOsrsIds($itemIds);

        $slots = [];
        foreach ($entries as $entry) {
            $slots[] = [
                'item' => $items[(int) $entry[0]] ?? null,
                'quantity' => (int) $entry[1],
            ];
        }

        return [
            'items' => $slots,
            'updated_at' => $this->updated_at,
        ];
    }
}
