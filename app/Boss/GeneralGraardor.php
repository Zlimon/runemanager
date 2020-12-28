<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\GeneralGraardor
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $pet_general_graardor
 * @property int $bandos_chestplate
 * @property int $bandos_tassets
 * @property int $bandos_boots
 * @property int $bandos_hilt
 * @property int $godsword_shard_1
 * @property int $godsword_shard_2
 * @property int $godsword_shard_3
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor query()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor whereBandosBoots($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor whereBandosChestplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor whereBandosHilt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor whereBandosTassets($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor whereGodswordShard1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor whereGodswordShard2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor whereGodswordShard3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor wherePetGeneralGraardor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralGraardor whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GeneralGraardor extends Model
{
    protected $table = 'general_graardor';

    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_general_graardor',
        'bandos_chestplate',
        'bandos_tassets',
        'bandos_boots',
        'bandos_hilt',
        'godsword_shard_1',
        'godsword_shard_2',
        'godsword_shard_3',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
