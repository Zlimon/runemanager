<?php

namespace App\Http\Resources;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $allItemIds = collect($this->bank)
            ->flatten(1)
            ->pluck(0)
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->all();

        $items = Item::lookupByOsrsIds($allItemIds);

        $bank = [];
        foreach ($this->bank as $tabIndex => $tabs) {
            $tabKey = 'tab-'.($tabIndex + 1);
            $bank[$tabKey] = [];

            foreach ($tabs as $itemIndex => $bankItem) {
                $itemId = (int) $bankItem[0];
                $quantity = (int) $bankItem[1];

                $bank[$tabKey][$itemIndex] = [
                    'item' => $items[$itemId] ?? null,
                    'quantity' => $quantity,
                ];
            }
        }

        return [
            'id' => $this->_id,
            'account_id' => $this->account_id,
            'tabs' => $bank,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
