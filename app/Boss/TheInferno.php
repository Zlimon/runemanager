<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\TheInferno
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $jal-nib-rek
 * @property int $infernal_cape
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|TheInferno newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheInferno newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheInferno query()
 * @method static \Illuminate\Database\Eloquent\Builder|TheInferno whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheInferno whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheInferno whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheInferno whereInfernalCape($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheInferno whereJalNibRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheInferno whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheInferno whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheInferno whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheInferno whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TheInferno extends Model
{
    protected $table = 'the_inferno';

    protected $fillable = [
        'obtained',
        'kill_count',
        'jal-nib-rek',
        'infernal_cape',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
