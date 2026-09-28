<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A Wise Old Man personal record: the account's best gain in one metric over
 * one period (`day`, `week`, `month`, `year`), synced by `SyncWomMilestones`.
 * `achieved_at` is when the record was last beaten.
 */
#[Fillable(['account_id', 'period', 'metric', 'value', 'achieved_at'])]
class WomRecord extends Model
{
    use HasFactory;

    /** The periods we store; WOM's five-minute records are skipped. */
    public const PERIODS = ['day', 'week', 'month', 'year'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'float',
            'achieved_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
