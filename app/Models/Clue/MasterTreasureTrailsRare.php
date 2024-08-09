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
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrailsRare newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrailsRare newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrailsRare query()
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrailsRare whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrailsRare whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrailsRare whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrailsRare whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrailsRare whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrailsRare whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MasterTreasureTrailsRare extends Model
{
    protected $table = 'master_treasure_trails_rare';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}