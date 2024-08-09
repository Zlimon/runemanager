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
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests query()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class BarrowsChests extends Model
{
    protected $table = 'barrows_chests';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}