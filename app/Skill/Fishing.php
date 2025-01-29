<?php

namespace App\Skill;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Skill\Fishing
 *
 * @property int $id
 * @property int $account_id
 * @property int $rank
 * @property int $level
 * @property int $xp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Fishing newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Fishing newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Fishing query()
 * @method static \Illuminate\Database\Eloquent\Builder|Fishing whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fishing whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fishing whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fishing whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fishing whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fishing whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fishing whereXp($value)
 * @mixin \Eloquent
 */
class Fishing extends Model
{
    protected $table = 'fishing';

    protected $fillable = ['level'];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
