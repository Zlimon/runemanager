<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\Bryophyta
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $bryophytas_essence
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Bryophyta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Bryophyta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Bryophyta query()
 * @method static \Illuminate\Database\Eloquent\Builder|Bryophyta whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bryophyta whereBryophytasEssence($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bryophyta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bryophyta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bryophyta whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bryophyta whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bryophyta whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bryophyta whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Bryophyta extends Model
{
    protected $table = 'bryophyta';

    protected $fillable = [
        'obtained',
        'kill_count',
        'bryophytas_essence',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
