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
 * @method static \Illuminate\Database\Eloquent\Builder|TheWhisperer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheWhisperer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheWhisperer query()
 * @method static \Illuminate\Database\Eloquent\Builder|TheWhisperer whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheWhisperer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheWhisperer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheWhisperer whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheWhisperer whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheWhisperer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TheWhisperer extends Model
{
    protected $table = 'the_whisperer';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}