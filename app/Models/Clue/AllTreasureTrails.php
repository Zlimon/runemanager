<?php

namespace App\Models\Clue;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllTreasureTrails extends Model
{
    protected $table = 'all_treasure_trails';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}