<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\Scorpia
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $scorpias_offspring
 * @property int $odium_shard_3
 * @property int $malediction_shard_3
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Scorpia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Scorpia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Scorpia query()
 * @method static \Illuminate\Database\Eloquent\Builder|Scorpia whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scorpia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scorpia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scorpia whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scorpia whereMaledictionShard3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scorpia whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scorpia whereOdiumShard3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scorpia whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scorpia whereScorpiasOffspring($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scorpia whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Scorpia extends Model
{
    protected $table = 'scorpia';

    protected $fillable = [
        'obtained',
        'kill_count',
        'scorpias_offspring',
        'odium_shard_3',
        'malediction_shard_3',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
