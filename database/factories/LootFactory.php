<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Item;
use App\Models\Loot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * One loot-drop document (SPEC §5.2). Items are pulled from the live item store
 * so they hydrate with real names/icons on the website.
 *
 * @extends Factory<Loot>
 */
class LootFactory extends Factory
{
    private const SOURCES = [
        ['source' => 'Zulrah', 'type' => 'npc'],
        ['source' => 'Vorkath', 'type' => 'npc'],
        ['source' => 'Cerberus', 'type' => 'npc'],
        ['source' => 'Chambers of Xeric', 'type' => 'raid'],
        ['source' => 'Theatre of Blood', 'type' => 'raid'],
        ['source' => 'Barrows', 'type' => 'chest'],
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $source = $this->faker->randomElement(self::SOURCES);
        $ids = Item::randomItemIds($this->faker->numberBetween(1, 4));

        $items = array_map(fn (int $id) => [
            'id' => $id,
            'quantity' => $this->faker->numberBetween(1, 50),
        ], $ids);

        return [
            'account_id' => Account::factory(),
            'source' => $source['source'],
            'type' => $source['type'],
            'items' => $items,
            'total_value' => $this->faker->numberBetween(1_000, 10_000_000),
            'killed_at' => now()->subMinutes($this->faker->numberBetween(0, 40_000)),
        ];
    }
}
