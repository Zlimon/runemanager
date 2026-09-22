<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Item;
use App\Models\Loot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * One loot-drop document. Items are pulled from the live item store
 * so they hydrate with real names/icons on the website.
 *
 * @extends Factory<Loot>
 */
class LootFactory extends Factory
{
    /**
     * Types are RuneLite's LootRecordType values, as the plugin sends them.
     * Raids and the Barrows chest are not types of their own — the Loot Tracker
     * records them as EVENT.
     */
    private const SOURCES = [
        ['source' => 'Zulrah', 'type' => 'NPC'],
        ['source' => 'Vorkath', 'type' => 'NPC'],
        ['source' => 'Cerberus', 'type' => 'NPC'],
        ['source' => 'Chambers of Xeric', 'type' => 'EVENT'],
        ['source' => 'Theatre of Blood', 'type' => 'EVENT'],
        ['source' => 'Barrows', 'type' => 'EVENT'],
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
