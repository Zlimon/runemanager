<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\DagannothKings
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $obtained
 * @property int $pet_dagannoth_prime
 * @property int $pet_dagannoth_supreme
 * @property int $pet_dagannoth_rex
 * @property int $berserker_ring
 * @property int $archers_ring
 * @property int $seers_ring
 * @property int $warrior_ring
 * @property int $dragon_axe
 * @property int $seercull
 * @property int $mud_battlestaff
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothKings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothKings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothKings query()
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothKings whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothKings whereArchersRing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothKings whereBerserkerRing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothKings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothKings whereDragonAxe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothKings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothKings whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothKings whereMudBattlestaff($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothKings whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothKings wherePetDagannothPrime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothKings wherePetDagannothRex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothKings wherePetDagannothSupreme($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothKings whereSeercull($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothKings whereSeersRing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothKings whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothKings whereWarriorRing($value)
 * @mixin \Eloquent
 */
class DagannothKings extends Model
{
    protected $table = 'dagannoth_kings';

    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_dagannoth_prime',
        'pet_dagannoth_supreme',
        'pet_dagannoth_rex',
        'berserker_ring',
        'archers_ring',
        'seers_ring',
        'warrior_ring',
        'dragon_axe',
        'seercull',
        'mud_battlestaff',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
