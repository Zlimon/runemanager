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
 * @method static \Illuminate\Database\Eloquent\Builder|TheInferno newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheInferno newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheInferno query()
 * @method static \Illuminate\Database\Eloquent\Builder|TheInferno whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheInferno whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheInferno whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheInferno whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheInferno whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheInferno whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TheInferno extends Model
{
    protected $table = 'the_inferno';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}