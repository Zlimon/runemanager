<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\AccountCombatAchievement;
use App\Support\CombatAchievements;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccountCombatAchievement>
 */
class AccountCombatAchievementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'points' => fake()->numberBetween(0, 2000),
            'tiers' => CombatAchievements::normaliseTiers([
                'easy' => 27,
                'medium' => 29,
            ]),
        ];
    }
}
