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
 * @method static \Illuminate\Database\Eloquent\Builder|SoulWars newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SoulWars newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SoulWars query()
 * @method static \Illuminate\Database\Eloquent\Builder|SoulWars whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoulWars whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoulWars whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoulWars whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoulWars whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoulWars whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SoulWars extends Model
{
    protected $table = 'soul_wars';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}