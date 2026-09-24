<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\CollectionLog;
use App\Services\CollectionLog\CollectionLogSync;
use App\Services\CollectionLog\CollectionLogView;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A synced collection log in the shape {@see CollectionLogSync}
 * writes from TempleOSRS: overall progress counts plus the *obtained* items
 * keyed by category slug.
 *
 * The slugs and item ids are the real ones. TempleOSRS is a network dependency
 * and the full structure is only used to show missing slots, so
 * {@see CollectionLogView} falls back to the
 * obtained set when it can't be fetched — real ids keep that offline path
 * rendering actual names and icons instead of empty slots.
 *
 * `CollectionLog` has no `HasFactory` trait (it is written by the sync, never
 * by the app), so build it as `CollectionLogFactory::new()`.
 *
 * @extends Factory<CollectionLog>
 */
class CollectionLogFactory extends Factory
{
    protected $model = CollectionLog::class;

    /**
     * Real TempleOSRS category slugs with their real drop tables, in the order
     * a player is likely to fill them.
     *
     * @var array<string, list<int>>
     */
    private const CATEGORIES = [
        'barrows_chests' => [
            4708, 4710, 4712, 4714, // Ahrim's
            4716, 4718, 4720, 4722, // Dharok's
            4724, 4726, 4728, 4730, // Guthan's
            4732, 4734, 4736, 4738, // Karil's
            4745, 4747, 4749, 4751, // Torag's
            4753, 4755, 4757, 4759, // Verac's
        ],
        'zulrah' => [12922, 12932, 12927, 6571, 13200, 13201, 12936, 12921],
        'vorkath' => [22124, 11286, 22006, 22111, 22106, 21992],
        'cerberus' => [13231, 13229, 13227, 13233, 13245, 13247],
        'chambers_of_xeric' => [
            21034, 21079, 21000, 21012, 21015, 21018, 21021,
            21024, 13652, 21003, 21043, 20997, 20851,
        ],
        'theatre_of_blood' => [22326, 22327, 22328, 22477, 22324, 22481, 22486, 22473],
        'clue_scrolls_master' => [
            2577, 2581, 19730, 20011, 20014, 12424,
            10334, 10330, 10332, 10336, 10342, 10344, 10350, 23185,
        ],
    ];

    /**
     * Every collection log slot in the game, so `obtained`/`total` read like a
     * real account's rather than like a percentage of this factory's sample.
     */
    private const GAME_TOTAL_SLOTS = 1561;

    private const GAME_TOTAL_CATEGORIES = 60;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $items = $this->obtainedItems();
        $obtained = array_sum(array_map('count', $items));

        return [
            'account_id' => Account::factory(),
            'obtained' => $obtained,
            'total' => self::GAME_TOTAL_SLOTS,
            'categories_finished' => count(array_filter(
                $items,
                fn (array $entries, string $slug): bool => count($entries) === count(self::CATEGORIES[$slug]),
                ARRAY_FILTER_USE_BOTH,
            )),
            'categories_available' => self::GAME_TOTAL_CATEGORIES,
            'items' => $items,
            'fetched_at' => now()->subHours($this->faker->numberBetween(0, 20)),
        ];
    }

    /** A log filled in enough to top the leaderboard. */
    public function completionist(): static
    {
        return $this->state(function (): array {
            $items = [];
            foreach (self::CATEGORIES as $slug => $ids) {
                $items[$slug] = array_map(fn (int $id): array => $this->entry($id), $ids);
            }

            return [
                'items' => $items,
                'obtained' => array_sum(array_map('count', $items)),
                'categories_finished' => count($items),
            ];
        });
    }

    /**
     * A partial slice of each category — a few slugs are dropped entirely, so
     * the panel has categories at 0 as well as categories part-way through.
     *
     * @return array<string, list<array{id: int, count: int, date: string}>>
     */
    private function obtainedItems(): array
    {
        $slugs = $this->faker->randomElements(
            array_keys(self::CATEGORIES),
            $this->faker->numberBetween(3, count(self::CATEGORIES)),
        );

        $items = [];
        foreach ($slugs as $slug) {
            $ids = self::CATEGORIES[$slug];
            $take = $this->faker->numberBetween(1, count($ids));

            $items[$slug] = array_map(
                fn (int $id): array => $this->entry($id),
                $this->faker->randomElements($ids, $take),
            );
        }

        return $items;
    }

    /**
     * One obtained entry. `count` and `date` are what TempleOSRS returns per
     * slot and what the panel shows on hover.
     *
     * @return array{id: int, count: int, date: string}
     */
    private function entry(int $id): array
    {
        return [
            'id' => $id,
            'count' => $this->faker->numberBetween(1, 12),
            'date' => $this->faker->dateTimeBetween('-3 years')->format('Y-m-d H:i:s'),
        ];
    }
}
