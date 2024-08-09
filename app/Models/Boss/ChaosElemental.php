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
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosElemental newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosElemental newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosElemental query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosElemental whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosElemental whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosElemental whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosElemental whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosElemental whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosElemental whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ChaosElemental extends Model
{
    protected $table = 'chaos_elemental';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}