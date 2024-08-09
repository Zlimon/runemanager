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
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrailsRare newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrailsRare newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrailsRare query()
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrailsRare whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrailsRare whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrailsRare whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrailsRare whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrailsRare whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrailsRare whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EliteTreasureTrailsRare extends Model
{
    protected $table = 'elite_treasure_trails_rare';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}