<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Inventory;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A 28-slot inventory snapshot — slots are [itemId, quantity] pairs, matching
 * the plugin push shape. Some slots are left empty ([0, 0]).
 *
 * @extends Factory<Inventory>
 */
class InventoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filled = $this->faker->numberBetween(8, 28);
        $ids = Item::randomItemIds($filled);

        $slots = [];
        for ($i = 0; $i < 28; $i++) {
            $slots[] = $i < count($ids)
                ? [$ids[$i], $this->faker->numberBetween(1, 1000)]
                : [0, 0];
        }

        return [
            'account_id' => Account::factory(),
            'inventory' => $slots,
        ];
    }
}
