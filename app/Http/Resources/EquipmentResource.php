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
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'head' => $this->head,
            'head_item' => Item::find((string) $this->head), // Item::where('id', (string) $this->head)->value('name'),
            'cape' => $this->cape,
            'cape_item' => Item::find((string) $this->cape),
            'neck' => $this->neck,
            'neck_item' => Item::find((string) $this->neck),
            'ammo' => $this->ammo,
            'ammo_item' => Item::find((string) $this->ammo),
            'weapon' => $this->weapon,
            'weapon_item' => Item::find((string) $this->weapon),
            'body' => $this->body,
            'body_item' => Item::find((string) $this->body),
            'shield' => $this->shield,
            'shield_item' => Item::find((string) $this->shield),
            'legs' => $this->legs,
            'legs_item' => Item::find((string) $this->legs),
            'hands' => $this->hands,
            'hands_item' => Item::find((string) $this->hands),
            'feet' => $this->feet,
            'feet_item' => Item::find((string) $this->feet),
            'ring' => $this->ring,
            'ring_item' => Item::find((string) $this->ring),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
