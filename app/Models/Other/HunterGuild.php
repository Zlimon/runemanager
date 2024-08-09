<?php

namespace App\Models\Other;

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
 * @method static \Illuminate\Database\Eloquent\Builder|HunterGuild newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HunterGuild newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HunterGuild query()
 * @method static \Illuminate\Database\Eloquent\Builder|HunterGuild whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HunterGuild whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HunterGuild whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HunterGuild whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HunterGuild whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HunterGuild whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class HunterGuild extends Model
{
    protected $table = 'hunter_guild';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}