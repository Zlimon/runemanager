<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Bank;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A bank snapshot — an array of tabs, each an array of [itemId, quantity] pairs,
 * matching the plugin push shape.
 *
 * @extends Factory<Bank>
 */
class BankFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tabCount = $this->faker->numberBetween(1, 5);
        $tabs = [];

        for ($t = 0; $t < $tabCount; $t++) {
            $ids = Item::randomItemIds($this->faker->numberBetween(5, 25));
            $tabs[] = array_map(fn (int $id) => [$id, $this->faker->numberBetween(1, 100_000)], $ids);
        }

        return [
            'account_id' => Account::factory(),
            'bank' => $tabs,
        ];
    }
}
