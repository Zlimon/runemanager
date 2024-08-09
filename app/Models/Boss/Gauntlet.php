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
 * @method static \Illuminate\Database\Eloquent\Builder|Gauntlet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Gauntlet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Gauntlet query()
 * @method static \Illuminate\Database\Eloquent\Builder|Gauntlet whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gauntlet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gauntlet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gauntlet whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gauntlet whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gauntlet whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Gauntlet extends Model
{
    protected $table = 'gauntlet';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}