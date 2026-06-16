<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use MongoDB\Laravel\Eloquent\Model;

/**
 * Append-only loot history per OSRS account (SPEC §5.2 Loot).
 *
 * One document = one drop event. The plugin pushes individual entries on each
 * drop and the website queries this collection for the recent-drops panel
 * and (later) the live feed.
 *
 * @property int $account_id
 * @property string $source
 * @property ?string $type
 * @property array<int, array{id: int, quantity: int}> $items
 * @property int $total_value
 * @property Carbon $killed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Loot extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $primaryKey = '_id';

    protected $fillable = [
        'account_id',
        'source',
        'type',
        'items',
        'total_value',
        'killed_at',
    ];

    // Mongo returns array fields natively, so don't add an 'array' cast for
    // `items` — that would trigger json_decode on something that's already
    // an array. The driver handles scalar casts (int/datetime) fine.
    protected $casts = [
        'account_id' => 'int',
        'total_value' => 'int',
        'killed_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
