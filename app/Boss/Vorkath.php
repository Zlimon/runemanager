<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\Vorkath
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $vorki
 * @property int $vorkaths_head
 * @property int $draconic_visage
 * @property int $skeletal_visage
 * @property int $jar_of_decay
 * @property int $dragonbone_necklace
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath query()
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath whereDraconicVisage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath whereDragonboneNecklace($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath whereJarOfDecay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath whereSkeletalVisage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath whereVorkathsHead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath whereVorki($value)
 * @mixin \Eloquent
 */
class Vorkath extends Model
{
    protected $table = 'vorkath';

    protected $fillable = [
        'obtained',
        'kill_count',
        'vorki',
        'vorkaths_head',
        'draconic_visage',
        'skeletal_visage',
        'jar_of_decay',
        'dragonbone_necklace',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
