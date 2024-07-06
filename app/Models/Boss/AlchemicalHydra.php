<?php

namespace App\Models\Boss;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Boss\AlchemicalHydra
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $ikkle_hydra
 * @property int $hydras_claw
 * @property int $hydra_tail
 * @property int $hydra_leather
 * @property int $hydras_fang
 * @property int $hydras_eye
 * @property int $hydras_heart
 * @property int $dragon_knife
 * @property int $dragon_thrownaxe
 * @property int $jar_of_chemicals
 * @property int $alchemical_hydra_heads
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra query()
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra whereAlchemicalHydraHeads($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra whereDragonKnife($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra whereDragonThrownaxe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra whereHydraLeather($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra whereHydraTail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra whereHydrasClaw($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra whereHydrasEye($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra whereHydrasFang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra whereHydrasHeart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra whereIkkleHydra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra whereJarOfChemicals($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AlchemicalHydra extends Model
{
    protected $table = 'alchemical_hydra';

    protected $fillable = [
        'obtained',
        'kill_count',
        'ikkle_hydra',
        'hydras_claw',
        'hydra_tail',
        'hydra_leather',
        'hydras_fang',
        'hydras_eye',
        'hydras_heart',
        'dragon_knife',
        'dragon_thrownaxe',
        'jar_of_chemicals',
        'alchemical_hydra_heads',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
