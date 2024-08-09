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
 * @method static \Illuminate\Database\Eloquent\Builder|GiantMole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GiantMole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GiantMole query()
 * @method static \Illuminate\Database\Eloquent\Builder|GiantMole whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiantMole whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiantMole whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiantMole whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiantMole whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiantMole whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GiantMole extends Model
{
    protected $table = 'giant_mole';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}