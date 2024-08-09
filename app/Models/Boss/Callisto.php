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
 * @method static \Illuminate\Database\Eloquent\Builder|Callisto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Callisto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Callisto query()
 * @method static \Illuminate\Database\Eloquent\Builder|Callisto whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Callisto whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Callisto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Callisto whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Callisto whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Callisto whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Callisto extends Model
{
    protected $table = 'callisto';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}