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
 * @method static \Illuminate\Database\Eloquent\Builder|AerialFishing newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AerialFishing newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AerialFishing query()
 * @method static \Illuminate\Database\Eloquent\Builder|AerialFishing whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AerialFishing whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AerialFishing whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AerialFishing whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AerialFishing whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AerialFishing whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AerialFishing extends Model
{
    protected $table = 'aerial_fishing';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}