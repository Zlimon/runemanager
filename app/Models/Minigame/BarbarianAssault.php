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
 * @method static \Illuminate\Database\Eloquent\Builder|BarbarianAssault newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BarbarianAssault newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BarbarianAssault query()
 * @method static \Illuminate\Database\Eloquent\Builder|BarbarianAssault whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarbarianAssault whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarbarianAssault whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarbarianAssault whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarbarianAssault whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarbarianAssault whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class BarbarianAssault extends Model
{
    protected $table = 'barbarian_assault';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}