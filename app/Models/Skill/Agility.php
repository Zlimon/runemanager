<?php

namespace App\Models\Skill;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $account_id
 * @property int $rank
 * @property int $level
 * @property int $xp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Agility newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Agility newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Agility query()
 * @method static \Illuminate\Database\Eloquent\Builder|Agility whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agility whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agility whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agility whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agility whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agility whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Agility whereXp($value)
 * @mixin \Eloquent
 */
class Agility extends Model
{
    protected $table = 'agility';

    protected $fillable = [
        'rank',
        'level',
        'xp',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
