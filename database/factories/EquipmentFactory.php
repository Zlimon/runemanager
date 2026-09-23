<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Equipment;
use Database\Seeders\Support\ItemPool;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A worn-equipment snapshot. Each slot holds an item a player can actually
 * equip there, drawn from {@see ItemPool} — one `$sample` per slot for the
 * whole run rather than one per slot per account.
 *
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
{
    /** The columns, which are also the osrsreboxed `equipment.slot` values. */
    private const SLOTS = [
        'head', 'cape', 'neck', 'ammo', 'weapon', 'body',
        'shield', 'legs', 'hands', 'feet', 'ring',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $slots = [];
        foreach (self::SLOTS as $slot) {
            $slots[$slot] = ItemPool::slotId($slot, $this->faker);
        }

        return [
            'account_id' => Account::factory(),
            ...$slots,
        ];
    }
}
