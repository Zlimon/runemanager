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
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah query()
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Zulrah extends Model
{
    protected $table = 'zulrah';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}