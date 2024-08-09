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
 * @method static \Illuminate\Database\Eloquent\Builder|GuardiansOfTheRift newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuardiansOfTheRift newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GuardiansOfTheRift query()
 * @method static \Illuminate\Database\Eloquent\Builder|GuardiansOfTheRift whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuardiansOfTheRift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuardiansOfTheRift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuardiansOfTheRift whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuardiansOfTheRift whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GuardiansOfTheRift whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GuardiansOfTheRift extends Model
{
    protected $table = 'guardians_of_the_rift';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}