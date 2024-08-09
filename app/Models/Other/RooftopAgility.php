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
 * @method static \Illuminate\Database\Eloquent\Builder|RooftopAgility newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RooftopAgility newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RooftopAgility query()
 * @method static \Illuminate\Database\Eloquent\Builder|RooftopAgility whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RooftopAgility whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RooftopAgility whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RooftopAgility whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RooftopAgility whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RooftopAgility whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RooftopAgility extends Model
{
    protected $table = 'rooftop_agility';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}