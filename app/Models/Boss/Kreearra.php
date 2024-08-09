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
 * @method static \Illuminate\Database\Eloquent\Builder|Kreearra newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kreearra newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kreearra query()
 * @method static \Illuminate\Database\Eloquent\Builder|Kreearra whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kreearra whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kreearra whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kreearra whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kreearra whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kreearra whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Kreearra extends Model
{
    protected $table = 'kreearra';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}