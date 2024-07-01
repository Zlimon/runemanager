<?php

namespace App\Skill;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Skill\Herblore
 *
 * @property int $id
 * @property int $account_id
 * @property int $rank
 * @property int $level
 * @property int $xp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Herblore newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Herblore newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Herblore query()
 * @method static \Illuminate\Database\Eloquent\Builder|Herblore whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Herblore whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Herblore whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Herblore whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Herblore whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Herblore whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Herblore whereXp($value)
 * @mixin \Eloquent
 */
class Herblore extends Model
{
    protected $table = 'herblore';

    protected $fillable = ['level'];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
