<?php

namespace App\Models\Boss;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Boss\GrotesqueGuardians
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $noon
 * @property int $black_tourmaline_core
 * @property int $granite_gloves
 * @property int $granite_ring
 * @property int $granite_hammer
 * @property int $jar_of_stone
 * @property int $granite_dust
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians query()
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians whereBlackTourmalineCore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians whereGraniteDust($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians whereGraniteGloves($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians whereGraniteHammer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians whereGraniteRing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians whereJarOfStone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians whereNoon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GrotesqueGuardians extends Model
{
    protected $table = 'grotesque_guardians';

    protected $fillable = [
        'obtained',
        'kill_count',
        'noon',
        'black_tourmaline_core',
        'granite_gloves',
        'granite_ring',
        'granite_hammer',
        'jar_of_stone',
        'granite_dust',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
