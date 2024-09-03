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
 * @method static \Illuminate\Database\Eloquent\Builder|Hunter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Hunter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Hunter query()
 * @method static \Illuminate\Database\Eloquent\Builder|Hunter whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hunter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hunter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hunter whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hunter whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hunter whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hunter whereXp($value)
 * @mixin \Eloquent
 */
class Hunter extends Model
{
    protected $table = 'hunter';

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
