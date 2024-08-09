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
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails query()
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class HardTreasureTrails extends Model
{
    protected $table = 'hard_treasure_trails';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}