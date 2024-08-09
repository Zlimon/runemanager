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
 * @method static \Illuminate\Database\Eloquent\Builder|Spindel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Spindel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Spindel query()
 * @method static \Illuminate\Database\Eloquent\Builder|Spindel whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Spindel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Spindel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Spindel whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Spindel whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Spindel whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Spindel extends Model
{
    protected $table = 'spindel';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}