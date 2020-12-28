<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\AbyssalSire
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $abyssal_orphan
 * @property int $unsired
 * @property int $abyssal_head
 * @property int $bludgeon_spine
 * @property int $bludgeon_claw
 * @property int $bludgeon_axon
 * @property int $jar_of_miasma
 * @property int $abyssal_dagger
 * @property int $abyssal_whip
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire query()
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire whereAbyssalDagger($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire whereAbyssalHead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire whereAbyssalOrphan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire whereAbyssalWhip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire whereBludgeonAxon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire whereBludgeonClaw($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire whereBludgeonSpine($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire whereJarOfMiasma($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire whereUnsired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AbyssalSire whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AbyssalSire extends Model
{
    protected $table = 'abyssal_sire';

    protected $fillable = [
        'obtained',
        'kill_count',
        'abyssal_orphan',
        'unsired',
        'abyssal_head',
        'bludgeon_spine',
        'bludgeon_claw',
        'bludgeon_axon',
        'jar_of_miasma',
        'abyssal_dagger',
        'abyssal_whip',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
