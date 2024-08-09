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
 * @method static \Illuminate\Database\Eloquent\Builder|LastManStanding newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LastManStanding newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LastManStanding query()
 * @method static \Illuminate\Database\Eloquent\Builder|LastManStanding whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LastManStanding whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LastManStanding whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LastManStanding whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LastManStanding whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LastManStanding whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LastManStanding extends Model
{
    protected $table = 'last_man_standing';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}