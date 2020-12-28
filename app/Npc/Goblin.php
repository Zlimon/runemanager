<?php
// TODO remove later
namespace App\Npc;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Npc\Goblin
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $bones
 * @property int $water_rune
 * @property int $coins
 * @property int $hammer
 * @property int $beer
 * @property int $goblin_mail
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Goblin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Goblin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Goblin query()
 * @method static \Illuminate\Database\Eloquent\Builder|Goblin whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goblin whereBeer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goblin whereBones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goblin whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goblin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goblin whereGoblinMail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goblin whereHammer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goblin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goblin whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goblin whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goblin whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goblin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goblin whereWaterRune($value)
 * @mixin \Eloquent
 */
class Goblin extends Model
{
    protected $table = 'goblin';

    protected $fillable = [
        'obtained',
        'kill_count',
        'bones',
        'water_rune',
        'coins',
        'hammer',
        'beer',
        'goblin_mail',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
