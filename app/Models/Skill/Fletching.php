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
 * @method static \Illuminate\Database\Eloquent\Builder|Fletching newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Fletching newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Fletching query()
 * @method static \Illuminate\Database\Eloquent\Builder|Fletching whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fletching whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fletching whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fletching whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fletching whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fletching whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fletching whereXp($value)
 *
 * @mixin \Eloquent
 */
class Fletching extends Model
{
    protected $table = 'fletching';

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
