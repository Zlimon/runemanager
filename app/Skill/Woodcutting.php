<?php

namespace App\Skill;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Skill\Woodcutting
 *
 * @property int $id
 * @property int $account_id
 * @property int $rank
 * @property int $level
 * @property int $xp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Woodcutting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Woodcutting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Woodcutting query()
 * @method static \Illuminate\Database\Eloquent\Builder|Woodcutting whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Woodcutting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Woodcutting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Woodcutting whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Woodcutting whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Woodcutting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Woodcutting whereXp($value)
 * @mixin \Eloquent
 */
class Woodcutting extends Model
{
    protected $table = 'woodcutting';

    protected $fillable = ['level'];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
