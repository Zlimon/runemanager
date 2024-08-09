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
 * @method static \Illuminate\Database\Eloquent\Builder|KingBlackDragon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KingBlackDragon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KingBlackDragon query()
 * @method static \Illuminate\Database\Eloquent\Builder|KingBlackDragon whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KingBlackDragon whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KingBlackDragon whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KingBlackDragon whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KingBlackDragon whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KingBlackDragon whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class KingBlackDragon extends Model
{
    protected $table = 'king_black_dragon';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}