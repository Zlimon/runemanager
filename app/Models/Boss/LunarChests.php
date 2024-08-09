<?php

namespace App\Models\Boss;

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
 * @method static \Illuminate\Database\Eloquent\Builder|LunarChests newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LunarChests newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LunarChests query()
 * @method static \Illuminate\Database\Eloquent\Builder|LunarChests whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LunarChests whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LunarChests whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LunarChests whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LunarChests whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LunarChests whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LunarChests extends Model
{
    protected $table = 'lunar_chests';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}