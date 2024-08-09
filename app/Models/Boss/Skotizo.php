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
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo query()
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Skotizo extends Model
{
    protected $table = 'skotizo';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}