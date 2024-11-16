<?php

namespace App\Models\Skill;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $account_id
 * @property int $rank
 * @property int $level
 * @property int $xp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Account $account
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Crafting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Crafting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Crafting query()
 * @method static \Illuminate\Database\Eloquent\Builder|Crafting whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Crafting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Crafting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Crafting whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Crafting whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Crafting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Crafting whereXp($value)
 *
 * @mixin \Eloquent
 */
class Crafting extends Model
{
    protected $table = 'crafting';

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
