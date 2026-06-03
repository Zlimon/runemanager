<?php

namespace App\Http\Resources;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EquipmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $itemIds = array_filter([
            $this->head, $this->cape, $this->neck, $this->ammo,
            $this->weapon, $this->body, $this->shield, $this->legs,
            $this->hands, $this->feet, $this->ring,
        ]);

        $items = Item::lookupByOsrsIds(array_map(fn ($id) => (int) $id, $itemIds));
        $get = fn (?int $id) => $id !== null ? ($items[(int) $id] ?? null) : null;

        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'head' => $this->head,
            'head_item' => $get($this->head),
            'cape' => $this->cape,
            'cape_item' => $get($this->cape),
            'neck' => $this->neck,
            'neck_item' => $get($this->neck),
            'ammo' => $this->ammo,
            'ammo_item' => $get($this->ammo),
            'weapon' => $this->weapon,
            'weapon_item' => $get($this->weapon),
            'body' => $this->body,
            'body_item' => $get($this->body),
            'shield' => $this->shield,
            'shield_item' => $get($this->shield),
            'legs' => $this->legs,
            'legs_item' => $get($this->legs),
            'hands' => $this->hands,
            'hands_item' => $get($this->hands),
            'feet' => $this->feet,
            'feet_item' => $get($this->feet),
            'ring' => $this->ring,
            'ring_item' => $get($this->ring),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
