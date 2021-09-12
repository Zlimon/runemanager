<?php

namespace App\Skill;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Skill\Smithing
 *
 * @property int $id
 * @property int $account_id
 * @property int $rank
 * @property int $level
 * @property int $xp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Smithing newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Smithing newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Smithing query()
 * @method static \Illuminate\Database\Eloquent\Builder|Smithing whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Smithing whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Smithing whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Smithing whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Smithing whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Smithing whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Smithing whereXp($value)
 * @mixin \Eloquent
 */
class Smithing extends Model
{
    protected $table = 'smithing';

    protected $fillable = ['level'];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
