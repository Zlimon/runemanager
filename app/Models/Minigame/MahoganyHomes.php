<?php

namespace App\Models\Minigame;

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
 * @method static \Illuminate\Database\Eloquent\Builder|MahoganyHomes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MahoganyHomes newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MahoganyHomes query()
 * @method static \Illuminate\Database\Eloquent\Builder|MahoganyHomes whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MahoganyHomes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MahoganyHomes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MahoganyHomes whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MahoganyHomes whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MahoganyHomes whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MahoganyHomes extends Model
{
    protected $table = 'mahogany_homes';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}