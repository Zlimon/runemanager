<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $primaryKey = '_id';

    protected $casts = [
        'account_id' => 'int',
    ];

    protected $fillable = [
        'inventory',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
