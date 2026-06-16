<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Item;
use App\Models\LootingBag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A looting bag snapshot — [itemId, quantity] slots, matching the plugin push.
 *
 * @extends Factory<LootingBag>
 */
class LootingBagFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ids = Item::randomItemIds($this->faker->numberBetween(2, 12));

        $slots = array_map(fn (int $id) => [$id, $this->faker->numberBetween(1, 500)], $ids);

        return [
            'account_id' => Account::factory(),
            'looting_bag' => $slots,
        ];
    }
}
