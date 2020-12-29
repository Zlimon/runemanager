<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\TheNightmare
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $little_nightmare
 * @property int $inquisitors_mace
 * @property int $inquisitors_great_helm
 * @property int $inquisitors_hauberk
 * @property int $inquisitors_plateskirt
 * @property int $nightmare_staff
 * @property int $volatile_orb
 * @property int $harmonised_orb
 * @property int $eldritch_orb
 * @property int $jar_of_dreams
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare query()
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare whereEldritchOrb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare whereHarmonisedOrb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare whereInquisitorsGreatHelm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare whereInquisitorsHauberk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare whereInquisitorsMace($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare whereInquisitorsPlateskirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare whereJarOfDreams($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare whereLittleNightmare($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare whereNightmareStaff($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare whereVolatileOrb($value)
 * @mixin \Eloquent
 */
class TheNightmare extends Model
{
    protected $table = 'the_nightmare';

    protected $fillable = [
        'obtained',
        'kill_count',
        'little_nightmare',
        'inquisitors_mace',
        'inquisitors_great_helm',
        'inquisitors_hauberk',
        'inquisitors_plateskirt',
        'nightmare_staff',
        'volatile_orb',
        'harmonised_orb',
        'eldritch_orb',
        'jar_of_dreams',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
