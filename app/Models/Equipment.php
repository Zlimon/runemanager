<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Equipment extends Model
{
    use HasFactory;

    /**
     * Equipment column => OSRS KitType slot index, as sent by the RuneLite
     * plugin. Slots 6, 8 and 11 (arms, hair, jaw) are body kit, not gear.
     *
     * @var array<string, int>
     */
    public const PLUGIN_SLOTS = [
        'head' => 0,
        'cape' => 1,
        'neck' => 2,
        'weapon' => 3,
        'body' => 4,
        'shield' => 5,
        'legs' => 7,
        'hands' => 9,
        'feet' => 10,
        'ring' => 12,
        'ammo' => 13,
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
