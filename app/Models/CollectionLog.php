<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

class CollectionLog extends Model
{
    protected $connection = 'mongodb';

    protected $primaryKey = '_id';

    protected $fillable = [
        'account_id',
        'obtained',
        'total',
        'categories_finished',
        'categories_available',
        'items',
        'fetched_at',
    ];

    protected $casts = [
        'account_id' => 'int',
        'obtained' => 'int',
        'total' => 'int',
        'categories_finished' => 'int',
        'categories_available' => 'int',
        'items' => 'array',
        'fetched_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
