<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\UsernameHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UsernameHistory>
 */
class UsernameHistoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'old_username' => substr($this->faker->unique()->userName(), 0, 12),
            'new_username' => substr($this->faker->unique()->userName(), 0, 12),
            'detected_at' => now()->subDays($this->faker->numberBetween(1, 400)),
        ];
    }
}
