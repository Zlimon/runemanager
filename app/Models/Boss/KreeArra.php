<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\KreeArra
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $pet_kreearra
 * @property int $armadyl_helmet
 * @property int $armadyl_chestplate
 * @property int $armadyl_chainskirt
 * @property int $armadyl_hilt
 * @property int $godsword_shard_1
 * @property int $godsword_shard_2
 * @property int $godsword_shard_3
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|KreeArra newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KreeArra newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KreeArra query()
 * @method static \Illuminate\Database\Eloquent\Builder|KreeArra whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KreeArra whereArmadylChainskirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KreeArra whereArmadylChestplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KreeArra whereArmadylHelmet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KreeArra whereArmadylHilt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KreeArra whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KreeArra whereGodswordShard1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KreeArra whereGodswordShard2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KreeArra whereGodswordShard3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KreeArra whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KreeArra whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KreeArra whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KreeArra wherePetKreearra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KreeArra whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KreeArra whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class KreeArra extends Model
{
    protected $table = 'kreearra';

    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_kreearra',
        'armadyl_helmet',
        'armadyl_chestplate',
        'armadyl_chainskirt',
        'armadyl_hilt',
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
