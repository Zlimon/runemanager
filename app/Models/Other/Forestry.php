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
 * @method static \Illuminate\Database\Eloquent\Builder|Forestry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Forestry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Forestry query()
 * @method static \Illuminate\Database\Eloquent\Builder|Forestry whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Forestry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Forestry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Forestry whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Forestry whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Forestry whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Forestry extends Model
{
    protected $table = 'forestry';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}