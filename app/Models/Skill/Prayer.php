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
 * @method static \Illuminate\Database\Eloquent\Builder|Prayer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Prayer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Prayer query()
 * @method static \Illuminate\Database\Eloquent\Builder|Prayer whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prayer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prayer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prayer whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prayer whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prayer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prayer whereXp($value)
 *
 * @mixin \Eloquent
 */
class Prayer extends Model
{
    protected $table = 'prayer';

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
