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
 * @method static \Illuminate\Database\Eloquent\Builder|Nex newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Nex newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Nex query()
 * @method static \Illuminate\Database\Eloquent\Builder|Nex whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nex whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nex whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nex whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nex whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nex whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Nex extends Model
{
    protected $table = 'nex';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}