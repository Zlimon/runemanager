<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\CommanderZilyana
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $pet_zilyana
 * @property int $armadyl_crossbow
 * @property int $saradomin_hilt
 * @property int $saradomin_sword
 * @property int $saradomins_light
 * @property int $godsword_shard_1
 * @property int $godsword_shard_2
 * @property int $godsword_shard_3
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana query()
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana whereArmadylCrossbow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana whereGodswordShard1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana whereGodswordShard2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana whereGodswordShard3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana wherePetZilyana($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana whereSaradominHilt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana whereSaradominSword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana whereSaradominsLight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CommanderZilyana extends Model
{
    protected $table = 'commander_zilyana';

    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_zilyana',
        'armadyl_crossbow',
        'saradomin_hilt',
        'saradomin_sword',
        'saradomins_light',
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
