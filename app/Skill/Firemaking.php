<?php

namespace App\Skill;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Skill\Firemaking
 *
 * @property int $id
 * @property int $account_id
 * @property int $rank
 * @property int $level
 * @property int $xp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Firemaking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Firemaking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Firemaking query()
 * @method static \Illuminate\Database\Eloquent\Builder|Firemaking whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Firemaking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Firemaking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Firemaking whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Firemaking whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Firemaking whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Firemaking whereXp($value)
 * @mixin \Eloquent
 */
class Firemaking extends Model
{
    protected $table = 'firemaking';

    protected $fillable = ['level'];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
