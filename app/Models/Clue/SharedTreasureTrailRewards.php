<?php

namespace App\Models\Clue;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $account_id
 * @property int $obtained
 * @property int $kill_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|SharedTreasureTrailRewards newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SharedTreasureTrailRewards newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SharedTreasureTrailRewards query()
 * @method static \Illuminate\Database\Eloquent\Builder|SharedTreasureTrailRewards whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SharedTreasureTrailRewards whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SharedTreasureTrailRewards whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SharedTreasureTrailRewards whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SharedTreasureTrailRewards whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SharedTreasureTrailRewards whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SharedTreasureTrailRewards extends Model
{
    protected $table = 'shared_treasure_trail_rewards';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}