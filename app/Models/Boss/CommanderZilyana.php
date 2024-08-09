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
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana query()
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommanderZilyana whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CommanderZilyana extends Model
{
    protected $table = 'commander_zilyana';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}