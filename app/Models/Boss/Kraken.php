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
 * @method static \Illuminate\Database\Eloquent\Builder|Kraken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kraken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kraken query()
 * @method static \Illuminate\Database\Eloquent\Builder|Kraken whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kraken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kraken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kraken whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kraken whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kraken whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Kraken extends Model
{
    protected $table = 'kraken';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}