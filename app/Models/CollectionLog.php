<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

/**
 * An account's collection log, pulled daily from TempleOSRS by
 * `collection-log:sync`. One document per account.
 */
#[Connection('mongodb')]
#[Table(key: '_id')]
#[Fillable([
    'account_id',
    'obtained',
    'total',
    'categories_finished',
    'categories_available',
    'items',
    'fetched_at',
])]
class CollectionLog extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'account_id' => 'int',
            'obtained' => 'int',
            'total' => 'int',
            'categories_finished' => 'int',
            'categories_available' => 'int',
            'items' => 'array',
            'fetched_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
