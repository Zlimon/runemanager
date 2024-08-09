<?php

namespace App\Models\Other;

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
 * @method static \Illuminate\Database\Eloquent\Builder|ShayzienArmour newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShayzienArmour newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShayzienArmour query()
 * @method static \Illuminate\Database\Eloquent\Builder|ShayzienArmour whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShayzienArmour whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShayzienArmour whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShayzienArmour whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShayzienArmour whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShayzienArmour whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ShayzienArmour extends Model
{
    protected $table = 'shayzien_armour';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}