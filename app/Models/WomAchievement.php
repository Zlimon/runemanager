<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A Wise Old Man achievement ("99 Cooking", "500 Zulrah kills"), synced by
 * `SyncWomMilestones`. `achieved_at` is null when WOM does not know the date;
 * `accuracy` is WOM's uncertainty about it in milliseconds. Legacy rows are
 * achievements WOM retired (e.g. "Base 90 Stats (Pre-Sailing)").
 */
#[Fillable(['account_id', 'name', 'metric', 'measure', 'threshold', 'achieved_at', 'accuracy', 'legacy'])]
class WomAchievement extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'threshold' => 'int',
            'achieved_at' => 'datetime',
            'accuracy' => 'int',
            'legacy' => 'bool',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
