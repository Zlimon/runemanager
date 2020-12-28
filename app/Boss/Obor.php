<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\Obor
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $hill_giant_club
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Obor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Obor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Obor query()
 * @method static \Illuminate\Database\Eloquent\Builder|Obor whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Obor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Obor whereHillGiantClub($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Obor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Obor whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Obor whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Obor whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Obor whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Obor extends Model
{
    protected $table = 'obor';

    protected $fillable = [
        'obtained',
        'kill_count',
        'hill_giant_club',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
