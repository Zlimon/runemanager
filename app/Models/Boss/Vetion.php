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
 * @method static \Illuminate\Database\Eloquent\Builder|Vetion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vetion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vetion query()
 * @method static \Illuminate\Database\Eloquent\Builder|Vetion whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vetion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vetion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vetion whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vetion whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vetion whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Vetion extends Model
{
    protected $table = 'vetion';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}