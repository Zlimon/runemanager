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
 * @method static \Illuminate\Database\Eloquent\Builder|Cyclopes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cyclopes newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cyclopes query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cyclopes whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cyclopes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cyclopes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cyclopes whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cyclopes whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cyclopes whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Cyclopes extends Model
{
    protected $table = 'cyclopes';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}