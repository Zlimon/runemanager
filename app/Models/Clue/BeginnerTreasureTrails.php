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
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails query()
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class BeginnerTreasureTrails extends Model
{
    protected $table = 'beginner_treasure_trails';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}