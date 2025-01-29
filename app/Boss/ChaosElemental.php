<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\ChaosElemental
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $pet_chaos_elemental
 * @property int $dragon_pickaxe
 * @property int $dragon_2h_sword
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosElemental newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosElemental newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosElemental query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosElemental whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosElemental whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosElemental whereDragon2hSword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosElemental whereDragonPickaxe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosElemental whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosElemental whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosElemental whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosElemental wherePetChaosElemental($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosElemental whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosElemental whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ChaosElemental extends Model
{
    protected $table = 'chaos_elemental';

    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_chaos_elemental',
        'dragon_pickaxe',
        'dragon_2h_sword',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
