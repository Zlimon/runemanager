<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $primaryKey = '_id';

    protected $casts = [
        'account_id' => 'int',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
