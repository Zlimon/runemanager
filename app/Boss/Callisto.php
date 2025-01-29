<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\Callisto
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $callisto_cub
 * @property int $tyrannical_ring
 * @property int $dragon_pickaxe
 * @property int $dragon_2h_sword
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Callisto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Callisto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Callisto query()
 * @method static \Illuminate\Database\Eloquent\Builder|Callisto whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Callisto whereCallistoCub($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Callisto whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Callisto whereDragon2hSword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Callisto whereDragonPickaxe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Callisto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Callisto whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Callisto whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Callisto whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Callisto whereTyrannicalRing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Callisto whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Callisto extends Model
{
    protected $table = 'callisto';

    protected $fillable = [
        'obtained',
        'kill_count',
        'callisto_cub',
        'tyrannical_ring',
        'dragon_pickaxe',
        'dragon_2h_sword',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
