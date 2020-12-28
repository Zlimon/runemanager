<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\Vetion
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $vetion_jr
 * @property int $ring_of_the_gods
 * @property int $dragon_pickaxe
 * @property int $dragon_2h_sword
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Vetion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vetion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vetion query()
 * @method static \Illuminate\Database\Eloquent\Builder|Vetion whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vetion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vetion whereDragon2hSword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vetion whereDragonPickaxe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vetion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vetion whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vetion whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vetion whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vetion whereRingOfTheGods($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vetion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vetion whereVetionJr($value)
 * @mixin \Eloquent
 */
class Vetion extends Model
{
    protected $table = 'vetion';

    protected $fillable = [
        'obtained',
        'kill_count',
        'vetion_jr',
        'ring_of_the_gods',
        'dragon_pickaxe',
        'dragon_2h_sword',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
