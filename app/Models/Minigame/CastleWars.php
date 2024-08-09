<?php

namespace App\Models\Minigame;

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
 * @method static \Illuminate\Database\Eloquent\Builder|CastleWars newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CastleWars newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CastleWars query()
 * @method static \Illuminate\Database\Eloquent\Builder|CastleWars whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CastleWars whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CastleWars whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CastleWars whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CastleWars whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CastleWars whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CastleWars extends Model
{
    protected $table = 'castle_wars';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}