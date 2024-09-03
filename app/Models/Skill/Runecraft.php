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
 * @method static \Illuminate\Database\Eloquent\Builder|Runecraft newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Runecraft newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Runecraft query()
 * @method static \Illuminate\Database\Eloquent\Builder|Runecraft whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Runecraft whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Runecraft whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Runecraft whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Runecraft whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Runecraft whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Runecraft whereXp($value)
 * @mixin \Eloquent
 */
class Runecraft extends Model
{
    protected $table = 'runecraft';

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
