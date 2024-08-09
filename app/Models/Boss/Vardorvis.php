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
 * @method static \Illuminate\Database\Eloquent\Builder|Vardorvis newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vardorvis newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vardorvis query()
 * @method static \Illuminate\Database\Eloquent\Builder|Vardorvis whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vardorvis whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vardorvis whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vardorvis whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vardorvis whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vardorvis whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Vardorvis extends Model
{
    protected $table = 'vardorvis';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}