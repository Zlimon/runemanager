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
 * @method static \Illuminate\Database\Eloquent\Builder|TztokJad newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TztokJad newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TztokJad query()
 * @method static \Illuminate\Database\Eloquent\Builder|TztokJad whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TztokJad whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TztokJad whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TztokJad whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TztokJad whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TztokJad whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TztokJad extends Model
{
    protected $table = 'tztok_jad';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}