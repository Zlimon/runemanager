<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

/**
 * One day of an account's Wise Old Man numbers, keyed by (account_id, date):
 * the headline total exp, EHP and EHB, plus every metric WOM reported as
 * maps keyed by WOM's metric names — `skills` (experience, no `overall`,
 * which is `exp`), `bosses` (kills) and `activities` (score). Unranked
 * metrics are absent from the maps. Holds the last values WOM reported that
 * UTC day; written by `wom:sync` and its one-year backfill. The maps are
 * native subdocuments (read back as PHP arrays), so they carry no cast: an
 * `array` cast would store them as JSON strings.
 */
#[Connection('mongodb')]
#[Table('wom_daily_snapshots', key: '_id')]
#[Fillable(['account_id', 'date', 'exp', 'ehp', 'ehb', 'skills', 'bosses', 'activities'])]
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
