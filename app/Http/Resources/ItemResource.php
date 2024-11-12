<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->_id,
            'name' => $this->name,
            'last_updated' => $this->last_updated,
            'incomplete' => $this->incomplete,
            'members' => $this->members,
            'tradeable' => $this->tradeable,
            'tradeable_on_ge' => $this->tradeable_on_ge,
            'stackable' => $this->stackable,
            'stacked' => $this->stacked,
            'noted' => $this->noted,
            'noteable' => $this->noteable,
            'linked_id_item' => $this->linked_id_item,
            'linked_id_noted' => $this->linked_id_noted,
            'linked_id_placeholder' => $this->linked_id_placeholder,
            'placeholder' => $this->placeholder,
            'equipable' => $this->equipable,
            'equipable_by_player' => $this->equipable_by_player,
            'equipable_weapon' => $this->equipable_weapon,
            'cost' => $this->cost,
            'lowalch' => $this->lowalch,
            'highalch' => $this->highalch,
            'weight' => $this->weight,
            'buy_limit' => $this->buy_limit,
            'quest_item' => $this->quest_item,
            'release_date' => $this->release_date,
            'duplicate' => $this->duplicate,
            'examine' => $this->examine,
            'icon' => $this->icon,
            'wiki_name' => $this->wiki_name,
            'wiki_url' => $this->wiki_url,
            'equipment' => $this->equipment,
            'weapon' => $this->weapon,
        ];
    }
}
