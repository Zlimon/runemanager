<?php

namespace App\Http\Resources;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EquipmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $itemIds = [
            $this->head,
            $this->cape,
            $this->neck,
            $this->ammo,
            $this->weapon,
            $this->body,
            $this->shield,
            $this->legs,
            $this->hands,
            $this->feet,
            $this->ring,
        ];

        $items = Item::whereIn('_id', $itemIds)->get()->keyBy('_id');

        // Helper function to get item by ID or return null if not found
        $getItem = fn ($id) => $items->get($id);

        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'head' => $this->head,
            'head_item' => $getItem($this->head),
            'cape' => $this->cape,
            'cape_item' => $getItem($this->cape),
            'neck' => $this->neck,
            'neck_item' => $getItem($this->neck),
            'ammo' => $this->ammo,
            'ammo_item' => $getItem($this->ammo),
            'weapon' => $this->weapon,
            'weapon_item' => $getItem($this->weapon),
            'body' => $this->body,
            'body_item' => $getItem($this->body),
            'shield' => $this->shield,
            'shield_item' => $getItem($this->shield),
            'legs' => $this->legs,
            'legs_item' => $getItem($this->legs),
            'hands' => $this->hands,
            'hands_item' => $getItem($this->hands),
            'feet' => $this->feet,
            'feet_item' => $getItem($this->feet),
            'ring' => $this->ring,
            'ring_item' => $getItem($this->ring),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
