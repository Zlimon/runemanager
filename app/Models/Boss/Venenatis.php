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
 * @method static \Illuminate\Database\Eloquent\Builder|Venenatis newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Venenatis newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Venenatis query()
 * @method static \Illuminate\Database\Eloquent\Builder|Venenatis whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Venenatis whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Venenatis whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Venenatis whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Venenatis whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Venenatis whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Venenatis extends Model
{
    protected $table = 'venenatis';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}