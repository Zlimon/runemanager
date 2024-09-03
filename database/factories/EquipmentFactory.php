<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Equipment>
 */
class EquipmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'head' => (int) Item::where([['equipable_by_player', true], ['equipment.slot', 'head']])->whereNotNull('release_date')->get()->pluck('id')->random(),
            'cape' => (int) Item::where([['equipable_by_player', true], ['equipment.slot', 'cape']])->whereNotNull('release_date')->get()->pluck('id')->random(),
            'neck' => (int) Item::where([['equipable_by_player', true], ['equipment.slot', 'neck']])->whereNotNull('release_date')->get()->pluck('id')->random(),
            'ammo' => (int) Item::where([['equipable_by_player', true], ['equipment.slot', 'ammo']])->whereNotNull('release_date')->get()->pluck('id')->random(),
            'weapon' => (int) Item::where([['equipable_by_player', true], ['equipment.slot', 'weapon']])->whereNotNull('release_date')->get()->pluck('id')->random(),
            'body' => (int) Item::where([['equipable_by_player', true], ['equipment.slot', 'body']])->whereNotNull('release_date')->get()->pluck('id')->random(),
            'shield' => (int) Item::where([['equipable_by_player', true], ['equipment.slot', 'shield']])->whereNotNull('release_date')->get()->pluck('id')->random(),
            'legs' => (int) Item::where([['equipable_by_player', true], ['equipment.slot', 'legs']])->whereNotNull('release_date')->get()->pluck('id')->random(),
            'hands' => (int) Item::where([['equipable_by_player', true], ['equipment.slot', 'hands']])->whereNotNull('release_date')->get()->pluck('id')->random(),
            'feet' => (int) Item::where([['equipable_by_player', true], ['equipment.slot', 'feet']])->whereNotNull('release_date')->get()->pluck('id')->random(),
            'ring' => (int) Item::where([['equipable_by_player', true], ['equipment.slot', 'ring']])->whereNotNull('release_date')->get()->pluck('id')->random(),
        ];
    }
}
