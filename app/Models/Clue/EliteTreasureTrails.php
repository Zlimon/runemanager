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
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails query()
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EliteTreasureTrails extends Model
{
    protected $table = 'elite_treasure_trails';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}