<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\Tempoross
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Tempoross newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tempoross newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tempoross query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tempoross whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tempoross whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tempoross whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tempoross whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tempoross whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tempoross whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tempoross whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Tempoross extends Model
{
    protected $table = 'tempoross';

    protected $fillable = [
        'obtained',
        'kill_count'
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
