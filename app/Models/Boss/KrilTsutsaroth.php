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
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth query()
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class KrilTsutsaroth extends Model
{
    protected $table = 'kril_tsutsaroth';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}