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
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails query()
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EasyTreasureTrails extends Model
{
    protected $table = 'easy_treasure_trails';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}