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
 * @method static \Illuminate\Database\Eloquent\Builder|RandomEvents newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RandomEvents newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RandomEvents query()
 * @method static \Illuminate\Database\Eloquent\Builder|RandomEvents whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RandomEvents whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RandomEvents whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RandomEvents whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RandomEvents whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RandomEvents whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RandomEvents extends Model
{
    protected $table = 'random_events';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}