<?php

namespace App\Skill;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Skill\Magic
 *
 * @property int $id
 * @property int $account_id
 * @property int $rank
 * @property int $level
 * @property int $xp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Magic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Magic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Magic query()
 * @method static \Illuminate\Database\Eloquent\Builder|Magic whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Magic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Magic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Magic whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Magic whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Magic whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Magic whereXp($value)
 * @mixin \Eloquent
 */
class Magic extends Model
{
    protected $table = 'magic';

    protected $fillable = ['level'];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
