<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

/**
 * Append-only loot history per OSRS account.
 *
 * One document = one drop event. The plugin pushes individual entries on each
 * drop and the website queries this collection for the recent-drops panel
 * and (later) the live feed. `items` is a list of `{id, quantity}` entries.
 */
#[Connection('mongodb')]
#[Table(key: '_id')]
#[Fillable(['account_id', 'source', 'type', 'items', 'total_value', 'killed_at'])]
class Loot extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * Mongo returns array fields natively, so `items` has no 'array' cast —
     * that would json_decode something that's already an array.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'account_id' => 'int',
            'total_value' => 'int',
            'killed_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
