<?php

namespace App\Models\Boss;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Boss\ThermonuclearSmokeDevil
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $pet_smoke_devil
 * @property int $occult_necklace
 * @property int $smoke_battlestaff
 * @property int $dragon_chainbody
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|ThermonuclearSmokeDevil newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ThermonuclearSmokeDevil newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ThermonuclearSmokeDevil query()
 * @method static \Illuminate\Database\Eloquent\Builder|ThermonuclearSmokeDevil whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThermonuclearSmokeDevil whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThermonuclearSmokeDevil whereDragonChainbody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThermonuclearSmokeDevil whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThermonuclearSmokeDevil whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThermonuclearSmokeDevil whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThermonuclearSmokeDevil whereOccultNecklace($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThermonuclearSmokeDevil wherePetSmokeDevil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThermonuclearSmokeDevil whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThermonuclearSmokeDevil whereSmokeBattlestaff($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThermonuclearSmokeDevil whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ThermonuclearSmokeDevil extends Model
{
    protected $table = 'thermonuclear_smoke_devil';

    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_smoke_devil',
        'occult_necklace',
        'smoke_battlestaff',
        'dragon_chainbody',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
