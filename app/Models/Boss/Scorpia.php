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
 * @method static \Illuminate\Database\Eloquent\Builder|Scorpia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Scorpia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Scorpia query()
 * @method static \Illuminate\Database\Eloquent\Builder|Scorpia whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scorpia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scorpia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scorpia whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scorpia whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Scorpia whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Scorpia extends Model
{
    protected $table = 'scorpia';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}