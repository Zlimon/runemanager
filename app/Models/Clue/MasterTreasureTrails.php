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
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails query()
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MasterTreasureTrails extends Model
{
    protected $table = 'master_treasure_trails';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}