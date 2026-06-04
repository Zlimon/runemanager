<?php

namespace Database\Factories;

use App\Enums\AccountTypesEnum;
use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = array_map(fn ($case) => $case->value, AccountTypesEnum::cases());

        return [
            'user_id' => User::factory(),
            // accounts.account_hash has a unique constraint; a short random
            // marker keeps factory rows from colliding when many are created.
            'account_hash' => 'fac-'.Str::lower(Str::random(20)),
            'account_type' => $this->faker->randomElement($types),
            // The schema caps usernames at 13 chars and enforces uniqueness, so
            // pull a unique username from the faker pool and trim defensively.
            'username' => substr($this->faker->unique()->userName(), 0, 13),
            'rank' => $this->faker->numberBetween(1, 2_000_000),
            'level' => $this->faker->numberBetween(32, 2277),
            'xp' => $this->faker->numberBetween(0, 200_000_000),
            // Presence is derived from last_seen_at: a recent stamp reads as
            // online, an old one as offline.
            'last_seen_at' => $this->faker->boolean()
                ? now()->subSeconds($this->faker->numberBetween(0, 120))
                : now()->subHours($this->faker->numberBetween(1, 48)),
        ];
    }
}
