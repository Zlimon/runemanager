<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $accountTypes = array_map(function ($accountType) {
            return $accountType->value;
        }, \App\Enums\AccountTypesEnum::cases()
        );

        return [
            'account_type' => $accountTypes[array_rand($accountTypes)],
            'username' => substr($this->faker->userName, 0, 13), // Limited to 13 characters
            'rank' => rand(1, 2000),
            'level' => rand(32, 2277),
            'xp' => rand(0, 200000000),
            'online' => $this->faker->boolean,
        ];
    }
}
