<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\Wintertodt
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $phoenix
 * @property int $tome_of_fire_(empty)
 * @property int $burnt_page
 * @property int $pyromancer_garb
 * @property int $pyromancer_hood
 * @property int $pyromancer_robe
 * @property int $pyromancer_boots
 * @property int $warm_gloves
 * @property int $bruma_torch
 * @property int $dragon_axe
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt query()
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt whereBrumaTorch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt whereBurntPage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt whereDragonAxe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt wherePhoenix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt wherePyromancerBoots($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt wherePyromancerGarb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt wherePyromancerHood($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt wherePyromancerRobe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt whereTomeOfFire(empty)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt whereWarmGloves($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt whereTomeOfFire(empty)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt whereTomeOfFire(empty)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt whereTomeOfFire(empty)($value)
 */
class Wintertodt extends Model
{
    protected $table = 'wintertodt';

    protected $fillable = [
        'obtained',
        'kill_count',
        'phoenix',
        'tome_of_fire_(empty)',
        'burnt_page',
        'pyromancer_garb',
        'pyromancer_hood',
        'pyromancer_robe',
        'pyromancer_boots',
        'warm_gloves',
        'bruma_torch',
        'dragon_axe',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
