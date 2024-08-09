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
 * @method static \Illuminate\Database\Eloquent\Builder|Tzhaar newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tzhaar newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tzhaar query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tzhaar whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tzhaar whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tzhaar whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tzhaar whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tzhaar whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tzhaar whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Tzhaar extends Model
{
    protected $table = 'tzhaar';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}