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
 * @method static \Illuminate\Database\Eloquent\Builder|Calvarion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Calvarion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Calvarion query()
 * @method static \Illuminate\Database\Eloquent\Builder|Calvarion whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Calvarion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Calvarion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Calvarion whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Calvarion whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Calvarion whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Calvarion extends Model
{
    protected $table = 'calvarion';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}