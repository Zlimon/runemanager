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
 * @method static \Illuminate\Database\Eloquent\Builder|Miscellaneous newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Miscellaneous newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Miscellaneous query()
 * @method static \Illuminate\Database\Eloquent\Builder|Miscellaneous whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Miscellaneous whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Miscellaneous whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Miscellaneous whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Miscellaneous whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Miscellaneous whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Miscellaneous extends Model
{
    protected $table = 'miscellaneous';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}