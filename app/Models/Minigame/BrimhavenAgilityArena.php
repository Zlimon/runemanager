<?php

namespace App\Models\Minigame;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $account_id
 * @property int $obtained
 * @property int $kill_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|BrimhavenAgilityArena newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BrimhavenAgilityArena newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BrimhavenAgilityArena query()
 * @method static \Illuminate\Database\Eloquent\Builder|BrimhavenAgilityArena whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrimhavenAgilityArena whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrimhavenAgilityArena whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrimhavenAgilityArena whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrimhavenAgilityArena whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrimhavenAgilityArena whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class BrimhavenAgilityArena extends Model
{
    protected $table = 'brimhaven_agility_arena';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}