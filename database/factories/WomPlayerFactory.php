<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\WomPlayer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A linked, active Wise Old Man profile as `SyncWomPlayer` stores it.
 *
 * @extends Factory<WomPlayer>
 */
class WomPlayerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $exp = fake()->numberBetween(5_000_000, 1_500_000_000);

        return [
            'account_id' => Account::factory(),
            'wom_player_id' => fake()->unique()->numberBetween(1, 5_000_000),
            'type' => fake()->randomElement(['regular', 'ironman', 'hardcore', 'ultimate']),
            'build' => 'main',
            'status' => 'active',
            'exp' => $exp,
            'ehp' => round($exp / 350_000, 4),
            'ehb' => fake()->randomFloat(4, 0, 1500),
            'ttm' => fake()->randomFloat(4, 0, 1500),
            'tt200m' => fake()->randomFloat(4, 5000, 20000),
            'combat_level' => fake()->numberBetween(60, 126),
            'registered_at' => now()->subYears(2),
            'wom_updated_at' => now()->subHours(3),
            'last_changed_at' => now()->subDays(1),
            'synced_at' => now()->subHours(3),
            'backfilled_at' => now()->subDays(30),
        ];
    }

    /** WOM has never tracked this account: the lookup 404'd. */
    public function notFound(): static
    {
        return $this->state(fn (): array => [
            'wom_player_id' => null,
            'type' => null,
            'build' => null,
            'status' => null,
            'exp' => 0,
            'ehp' => 0,
            'ehb' => 0,
            'ttm' => 0,
            'tt200m' => 0,
            'combat_level' => null,
            'registered_at' => null,
            'wom_updated_at' => null,
            'last_changed_at' => null,
            'backfilled_at' => null,
            'not_found_at' => now()->subDay(),
            'synced_at' => now()->subDay(),
        ]);
    }

    /** Linked, but gained nothing for months. */
    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'last_changed_at' => now()->subMonths(6),
            'wom_updated_at' => now()->subMonths(6),
        ]);
    }
}
