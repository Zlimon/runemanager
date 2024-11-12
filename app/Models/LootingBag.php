<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

class LootingBag extends Model
{
    protected $connection = 'mongodb-client';

    protected $primaryKey = '_id';

    protected $casts = [
        '_id' => 'int',
        'account_id' => 'int',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
