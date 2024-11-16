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
 * @method static \Illuminate\Database\Eloquent\Builder|Herblore newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Herblore newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Herblore query()
 * @method static \Illuminate\Database\Eloquent\Builder|Herblore whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Herblore whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Herblore whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Herblore whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Herblore whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Herblore whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Herblore whereXp($value)
 *
 * @mixin \Eloquent
 */
class Herblore extends Model
{
    protected $table = 'herblore';

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
