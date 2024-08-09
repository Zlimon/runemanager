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
 * @method static \Illuminate\Database\Eloquent\Builder|Hespori newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Hespori newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Hespori query()
 * @method static \Illuminate\Database\Eloquent\Builder|Hespori whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hespori whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hespori whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hespori whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hespori whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hespori whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Hespori extends Model
{
    protected $table = 'hespori';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}