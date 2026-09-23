<?php

namespace Database\Factories;

use App\Http\Controllers\GroupBankController;
use App\Models\GroupBank;
use Database\Seeders\Support\ItemPool;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * The shared Group Ironman group bank — one document per instance, overwritten
 * on each push, items stored as a flat `[[itemId, quantity], …]` list (the
 * in-game group storage has no tabs).
 *
 * Only meaningful in GROUP mode: {@see GroupBankController}
 * is the one reader and the page 404s in the other two modes.
 *
 * `GroupBank` has no `HasFactory` trait (the plugin writes it, the app never
 * does), so build it as `GroupBankFactory::new()`.
 *
 * @extends Factory<GroupBank>
 */
class GroupBankFactory extends Factory
{
    protected $model = GroupBank::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ids = ItemPool::ids($this->faker->numberBetween(40, 90), $this->faker);

        return [
            'items' => array_map(
                fn (int $id): array => [$id, $this->faker->numberBetween(1, 25_000)],
                $ids,
            ),
        ];
    }
}
