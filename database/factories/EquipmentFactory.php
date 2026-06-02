<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Equipment;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'head' => $this->randomItemIdForSlot('head'),
            'cape' => $this->randomItemIdForSlot('cape'),
            'neck' => $this->randomItemIdForSlot('neck'),
            'ammo' => $this->randomItemIdForSlot('ammo'),
            'weapon' => $this->randomItemIdForSlot('weapon'),
            'body' => $this->randomItemIdForSlot('body'),
            'shield' => $this->randomItemIdForSlot('shield'),
            'legs' => $this->randomItemIdForSlot('legs'),
            'hands' => $this->randomItemIdForSlot('hands'),
            'feet' => $this->randomItemIdForSlot('feet'),
            'ring' => $this->randomItemIdForSlot('ring'),
        ];
    }

    /**
     * Mongo's `$sample` aggregation: one query per slot, no full collection
     * load (the previous all-in-memory ->random() was O(items-in-slot) per call).
     */
    private function randomItemIdForSlot(string $slot): ?int
    {
        $doc = (new Item)->getConnection()
            ->getDatabase()
            ->selectCollection((new Item)->getTable())
            ->aggregate([
                ['$match' => [
                    'equipable_by_player' => true,
                    'equipment.slot' => $slot,
                    'release_date' => ['$ne' => null],
                ]],
                ['$sample' => ['size' => 1]],
                ['$project' => ['_id' => 1]],
            ])
            ->toArray()[0] ?? null;

        return $doc ? (int) $doc['_id'] : null;
    }
}
