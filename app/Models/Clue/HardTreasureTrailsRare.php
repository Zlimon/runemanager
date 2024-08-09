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
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrailsRare newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrailsRare newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrailsRare query()
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrailsRare whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrailsRare whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrailsRare whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrailsRare whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrailsRare whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrailsRare whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class HardTreasureTrailsRare extends Model
{
    protected $table = 'hard_treasure_trails_rare';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}