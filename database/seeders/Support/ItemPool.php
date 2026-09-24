<?php

namespace Database\Seeders\Support;

use App\Models\Item;
use Database\Factories\EquipmentFactory;
use Faker\Generator;
use Throwable;

/**
 * One sample of the static item catalogue per process, shared by every factory
 * that needs OSRS item ids.
 *
 * The factories used to run a `$sample` aggregation per document — a bank alone
 * fires one per tab — which put a few hundred round-trips to `mongodb-static`
 * in front of a full demo seed. Sampling a pool once and slicing it costs
 * twelve aggregations for the whole run (one general pool + one per equipment
 * slot) and, because the slicing is done with the caller's Faker generator, it
 * also makes the seed reproducible from `fake()->seed(...)`.
 *
 * The catalogue is optional: `docker/gamedata` may not have been populated, or
 * the connection may be down entirely. Both degrade to an empty pool — callers
 * get `[]`/`null` and seed empty inventories rather than throwing 25 times.
 */
final class ItemPool
{
    /** Distinct ids sampled for general use (banks, inventories, loot, feed). */
    public const SIZE = 500;

    /** Distinct ids sampled per equipment slot. */
    private const SLOT_SIZE = 12;

    /** @var list<int>|null */
    private static ?array $pool = null;

    /** @var array<string, list<int>> */
    private static array $slots = [];

    /**
     * Up to $count distinct item ids from the pool, or an empty list when the
     * catalogue is unavailable.
     *
     * @return list<int>
     */
    public static function ids(int $count, ?Generator $faker = null): array
    {
        $pool = self::pool();

        if ($count < 1 || $pool === []) {
            return [];
        }

        $count = min($count, count($pool));

        if ($faker !== null) {
            return array_values($faker->randomElements($pool, $count));
        }

        shuffle($pool);

        return array_slice($pool, 0, $count);
    }

    /** A single item id, or 0 when the catalogue is unavailable (as `Item::randomItemId()` returns). */
    public static function id(?Generator $faker = null): int
    {
        return self::ids(1, $faker)[0] ?? 0;
    }

    /**
     * An item a player can equip in $slot ('head', 'weapon', 'ring', …), or
     * null when the catalogue has nothing for it.
     */
    public static function slotId(string $slot, ?Generator $faker = null): ?int
    {
        $ids = self::$slots[$slot] ??= self::sampleSlot($slot);

        if ($ids === []) {
            return null;
        }

        return $faker !== null ? $faker->randomElement($ids) : $ids[array_rand($ids)];
    }

    /**
     * Whether the catalogue yielded anything. Seeders use this to warn once
     * ("item icons will be missing — run `sail run --rm mongodb-static-populate`")
     * instead of producing silently empty snapshots.
     */
    public static function isEmpty(): bool
    {
        return self::pool() === [];
    }

    /** Drop the memo so the next call re-samples. For tests. */
    public static function forget(): void
    {
        self::$pool = null;
        self::$slots = [];
    }

    /** @return list<int> */
    private static function pool(): array
    {
        return self::$pool ??= self::sample();
    }

    /** @return list<int> */
    private static function sample(): array
    {
        try {
            return Item::randomItemIds(self::SIZE);
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * The slot-filtered `$sample` that {@see EquipmentFactory}
     * used to run per slot per account.
     *
     * @return list<int>
     */
    private static function sampleSlot(string $slot): array
    {
        try {
            $docs = (new Item)->getConnection()
                ->getDatabase()
                ->selectCollection((new Item)->getTable())
                ->aggregate([
                    ['$match' => [
                        'equipable_by_player' => true,
                        'equipment.slot' => $slot,
                        'release_date' => ['$ne' => null],
                    ]],
                    ['$sample' => ['size' => self::SLOT_SIZE]],
                    // Use the OSRS `id` field, not Mongo's ObjectId `_id`.
                    ['$project' => ['id' => 1, '_id' => 0]],
                ])
                ->toArray();
        } catch (Throwable) {
            return [];
        }

        return array_values(array_map(fn ($doc): int => (int) ((array) $doc)['id'], $docs));
    }
}
