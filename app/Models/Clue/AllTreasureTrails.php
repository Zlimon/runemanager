<?php

namespace App\Models\Clue;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Clues\AllTreasureTrails
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|AllTreasureTrails newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AllTreasureTrails newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AllTreasureTrails query()
 * @method static \Illuminate\Database\Eloquent\Builder|AllTreasureTrails whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllTreasureTrails whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllTreasureTrails whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllTreasureTrails whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllTreasureTrails whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllTreasureTrails whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllTreasureTrails whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AllTreasureTrails extends Model
{
    protected $table = 'all_treasure_trails';

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
