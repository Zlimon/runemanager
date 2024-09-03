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
 * @method static \Illuminate\Database\Eloquent\Builder|Cooking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cooking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cooking query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cooking whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cooking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cooking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cooking whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cooking whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cooking whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cooking whereXp($value)
 * @mixin \Eloquent
 */
class Cooking extends Model
{
    protected $table = 'cooking';

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
