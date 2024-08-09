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
 * @method static \Illuminate\Database\Eloquent\Builder|KalphiteQueen newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KalphiteQueen newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KalphiteQueen query()
 * @method static \Illuminate\Database\Eloquent\Builder|KalphiteQueen whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KalphiteQueen whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KalphiteQueen whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KalphiteQueen whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KalphiteQueen whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KalphiteQueen whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class KalphiteQueen extends Model
{
    protected $table = 'kalphite_queen';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}