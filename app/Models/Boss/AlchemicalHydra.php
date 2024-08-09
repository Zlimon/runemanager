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
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra query()
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AlchemicalHydra whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AlchemicalHydra extends Model
{
    protected $table = 'alchemical_hydra';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}