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
 * @method static \Illuminate\Database\Eloquent\Builder|Mining newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Mining newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Mining query()
 * @method static \Illuminate\Database\Eloquent\Builder|Mining whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mining whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mining whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mining whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mining whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mining whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mining whereXp($value)
 *
 * @mixin \Eloquent
 */
class Mining extends Model
{
    protected $table = 'mining';

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
