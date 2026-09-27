<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

/**
 * One day of an account's Wise Old Man headline numbers (total exp, EHP,
 * EHB), keyed by (account_id, date). Holds the last value WOM reported that
 * UTC day; written by `wom:sync` and its one-year backfill.
 */
#[Connection('mongodb')]
#[Table('wom_daily_snapshots', key: '_id')]
#[Fillable(['account_id', 'date', 'exp', 'ehp', 'ehb'])]
class WomDailySnapshot extends Model
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
            'exp' => 'int',
            'ehp' => 'float',
            'ehb' => 'float',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
