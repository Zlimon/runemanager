<?php

namespace App\Http\Resources;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DropResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'], // item id
            'item' => new ItemResource(Item::where('id', (string) $this['id'])->first()),
            'name' => $this['name'],
            'members' => $this['members'],
            'noted' => $this['noted'],
            'quantity' => $this['quantity'],
            'rarity' => $this['rarity'],
            'rolls' => $this['rolls'],
        ];
    }
}
