<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\CrazyArchaeologist
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $odium_shard_2
 * @property int $malediction_shard_2
 * @property int $fedora
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|CrazyArchaeologist newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrazyArchaeologist newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrazyArchaeologist query()
 * @method static \Illuminate\Database\Eloquent\Builder|CrazyArchaeologist whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrazyArchaeologist whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrazyArchaeologist whereFedora($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrazyArchaeologist whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrazyArchaeologist whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrazyArchaeologist whereMaledictionShard2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrazyArchaeologist whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrazyArchaeologist whereOdiumShard2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrazyArchaeologist whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrazyArchaeologist whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CrazyArchaeologist extends Model
{
    protected $table = 'crazy_archaeologist';

    protected $fillable = [
        'obtained',
        'kill_count',
        'odium_shard_2',
        'malediction_shard_2',
        'fedora',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
