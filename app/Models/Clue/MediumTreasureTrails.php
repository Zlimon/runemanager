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
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails query()
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MediumTreasureTrails extends Model
{
    protected $table = 'medium_treasure_trails';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}