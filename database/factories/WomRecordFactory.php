<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\WomRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A Wise Old Man personal record as `SyncWomMilestones` stores it. Defaults to
 * a weekly experience record in a skill.
 *
 * @extends Factory<WomRecord>
 */
class WomRecordFactory extends Factory
{
    private const SKILLS = ['attack', 'strength', 'cooking', 'fishing', 'slayer', 'hunter', 'runecrafting'];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'period' => 'week',
            'metric' => fake()->randomElement(self::SKILLS),
            'value' => fake()->numberBetween(100_000, 5_000_000),
            'achieved_at' => now()->subDays(fake()->numberBetween(1, 365)),
        ];
    }

    /** The record for one period (`day`, `week`, `month`, `year`). */
    public function period(string $period): static
    {
        return $this->state(fn (): array => ['period' => $period]);
    }

    /** A kill-count record on a boss. */
    public function kills(string $boss = 'zulrah', int $kills = 150): static
    {
        return $this->state(fn (): array => ['metric' => $boss, 'value' => $kills]);
    }
}
