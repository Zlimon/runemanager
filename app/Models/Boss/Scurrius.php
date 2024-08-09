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
 * @method static \Illuminate\Database\Eloquent\Builder|Scurrius newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Scurrius newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Scurrius query()
 * @method static \Illuminate\Database\Eloquent\Builder|Scurrius whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scurrius whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scurrius whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scurrius whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scurrius whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scurrius whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Scurrius extends Model
{
    protected $table = 'scurrius';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}