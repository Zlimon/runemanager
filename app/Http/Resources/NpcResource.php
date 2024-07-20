<?php

namespace App\Http\Resources;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NpcResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $drops = $this->drop_items;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'last_updated' => $this->last_updated,
            'incomplete' => $this->incomplete,
            'members' => $this->members,
            'release_date' => $this->release_date,
            'combat_level' => $this->combat_level,
            'size' => $this->size,
            'hitpoints' => $this->hitpoints,
            'max_hit' => $this->max_hit,
            'attack_type' => $this->attack_type,
            'attack_speed' => $this->attack_speed,
            'aggressive' => $this->aggressive,
            'poisonous' => $this->poisonous,
            'venomous' => $this->venomous,
            'immune_poison' => $this->immune_poison,
            'immune_venom' => $this->immune_venom,
            'attributes' => $this->attributes,
            'category' => $this->category,
            'slayer_monster' => $this->slayer_monster,
            'slayer_level' => $this->slayer_level,
            'slayer_xp' => $this->slayer_xp,
            'slayer_masters' => $this->slayer_masters,
            'duplicate' => $this->duplicate,
            'examine' => $this->examine,
            'wiki_name' => $this->wiki_name,
            'wiki_url' => $this->wiki_url,
            'attack_level' => $this->attack_level,
            'strength_level' => $this->strength_level,
            'defence_level' => $this->defence_level,
            'magic_level' => $this->magic_level,
            'ranged_level' => $this->ranged_level,
            'attack_bonus' => $this->attack_bonus,
            'strength_bonus' => $this->strength_bonus,
            'attack_magic' => $this->attack_magic,
            'magic_bonus' => $this->magic_bonus,
            'attack_ranged' => $this->attack_ranged,
            'ranged_bonus' => $this->ranged_bonus,
            'defence_stab' => $this->defence_stab,
            'defence_slash' => $this->defence_slash,
            'defence_crush' => $this->defence_crush,
            'defence_magic' => $this->defence_magic,
            'defence_ranged' => $this->defence_ranged,
            'drops' => $this->drop_items,
//            'drops' => array_map(function ($drop) {
//                $item = Item::where('id', (string) $drop['id'])->first();
//
//                $item = (new ItemResource($item))->resolve();
//                $item['rarity'] = $drop['rarity'];
//
//                return $item;
//            }, $this->drops),
//        'drops' => $this->whenLoaded('drops'),
//            (new UserResource($this->whenLoaded('user')))
//        NpcResource::collection($npcs->with('drops')->paginate($perPage))
        ];
    }
}
