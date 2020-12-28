<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\TheFightCaves
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $tzrek-jad
 * @property int $fire_cape
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|TheFightCaves newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheFightCaves newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheFightCaves query()
 * @method static \Illuminate\Database\Eloquent\Builder|TheFightCaves whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheFightCaves whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheFightCaves whereFireCape($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheFightCaves whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheFightCaves whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheFightCaves whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheFightCaves whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheFightCaves whereTzrekJad($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheFightCaves whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TheFightCaves extends Model
{
    protected $table = 'the_fight_caves';

    protected $fillable = [
        'obtained',
        'kill_count',
        'tzrek-jad',
        'fire_cape',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
