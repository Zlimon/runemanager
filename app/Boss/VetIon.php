<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\VetIon
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
 * @method static \Illuminate\Database\Eloquent\Builder|VetIon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VetIon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VetIon query()
 * @method static \Illuminate\Database\Eloquent\Builder|VetIon whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VetIon whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VetIon whereDragon2hSword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VetIon whereDragonPickaxe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VetIon whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VetIon whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VetIon whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VetIon whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VetIon whereRingOfTheGods($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VetIon whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VetIon whereVetionJr($value)
 * @mixin \Eloquent
 */
class VetIon extends Model
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
