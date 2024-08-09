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
 * @method static \Illuminate\Database\Eloquent\Builder|ThermonuclearSmokeDevil newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ThermonuclearSmokeDevil newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ThermonuclearSmokeDevil query()
 * @method static \Illuminate\Database\Eloquent\Builder|ThermonuclearSmokeDevil whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThermonuclearSmokeDevil whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThermonuclearSmokeDevil whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThermonuclearSmokeDevil whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThermonuclearSmokeDevil whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThermonuclearSmokeDevil whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ThermonuclearSmokeDevil extends Model
{
    protected $table = 'thermonuclear_smoke_devil';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}