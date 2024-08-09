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
 * @method static \Illuminate\Database\Eloquent\Builder|Tempoross newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tempoross newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tempoross query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tempoross whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tempoross whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tempoross whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tempoross whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tempoross whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tempoross whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Tempoross extends Model
{
    protected $table = 'tempoross';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}