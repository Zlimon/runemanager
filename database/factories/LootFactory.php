<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Loot;
use App\Services\Loot\LootLeaderboard;
use Database\Seeders\Support\ItemPool;
use Illuminate\Database\Eloquent\Factories\Factory;
use InvalidArgumentException;

/**
 * One loot-drop document. Items are pulled from the live item store
 * so they hydrate with real names/icons on the website.
 *
 * @extends Factory<Loot>
 */
class LootFactory extends Factory
{
    /**
     * Types are RuneLite's LootRecordType values, as the plugin sends them, and
     * they cover every {@see LootLeaderboard::CATEGORIES} section so the loot
     * directory has no empty ones. Raids and the Barrows chest are not types of
     * their own — the Loot Tracker records them as EVENT.
     */
    private const SOURCES = [
        ['source' => 'Zulrah', 'type' => 'NPC'],
        ['source' => 'Vorkath', 'type' => 'NPC'],
        ['source' => 'Cerberus', 'type' => 'NPC'],
        ['source' => 'Chambers of Xeric', 'type' => 'EVENT'],
        ['source' => 'Theatre of Blood', 'type' => 'EVENT'],
        ['source' => 'Barrows', 'type' => 'EVENT'],
        ['source' => 'Master Farmer', 'type' => 'PICKPOCKET'],
        ['source' => 'H.A.M. Member', 'type' => 'PICKPOCKET'],
        ['source' => 'Elf', 'type' => 'PICKPOCKET'],
        // The Loot Tracker records a PvP kill under the defeated player's name.
        ['source' => 'Zezima', 'type' => 'PLAYER'],
        ['source' => 'B0aty', 'type' => 'PLAYER'],
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $source = $this->faker->randomElement(self::SOURCES);
        $ids = ItemPool::ids($this->faker->numberBetween(1, 4), $this->faker);

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

    /**
     * A drop from a source of one RuneLite record type ('NPC', 'EVENT',
     * 'PICKPOCKET', 'PLAYER'). Lets a seeder guarantee each loot-directory
     * section is populated rather than leaving it to the random source pick.
     */
    public function ofType(string $type): static
    {
        $sources = array_values(array_filter(
            self::SOURCES,
            fn (array $source): bool => $source['type'] === $type,
        ));

        if ($sources === []) {
            throw new InvalidArgumentException("LootFactory has no source of type [{$type}].");
        }

        return $this->state(fn (): array => $this->faker->randomElement($sources));
    }
}
