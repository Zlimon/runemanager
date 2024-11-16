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
 * @method static \Illuminate\Database\Eloquent\Builder|Strength newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Strength newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Strength query()
 * @method static \Illuminate\Database\Eloquent\Builder|Strength whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Strength whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Strength whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Strength whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Strength whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Strength whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Strength whereXp($value)
 *
 * @mixin \Eloquent
 */
class Strength extends Model
{
    protected $table = 'strength';

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
