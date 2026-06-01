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
        'slots',
    ];

    protected $casts = [
        'account_id' => 'int',
        'slots' => 'array',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
