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
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast query()
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorporealBeast whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CorporealBeast extends Model
{
    protected $table = 'corporeal_beast';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}