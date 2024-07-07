<?php

namespace App\Models\Boss;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Boss\TheGauntlet
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $youngllef
 * @property int $crystal_armour_seed
 * @property int $crystal_weapon_seed
 * @property int $blade_of_saeldor_(inactive)
 * @property int $gauntlet_cape
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet query()
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet whereBladeOfSaeldor(inactive)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet whereCrystalArmourSeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet whereCrystalWeaponSeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet whereGauntletCape($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet whereYoungllef($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet whereBladeOfSaeldor(inactive)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet whereBladeOfSaeldor(inactive)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet whereBladeOfSaeldor(inactive)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet whereBladeOfSaeldor(inactive)($value)
 * @mixin \Eloquent
 */
class TheGauntlet extends Model
{
    protected $table = 'the_gauntlet';

    protected $fillable = [
        'obtained',
        'kill_count',
        'youngllef',
        'crystal_armour_seed',
        'crystal_weapon_seed',
        'blade_of_saeldor_(inactive)',
        'gauntlet_cape',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
