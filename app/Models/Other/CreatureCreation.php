<?php

namespace App\Models\Other;

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
 * @method static \Illuminate\Database\Eloquent\Builder|CreatureCreation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CreatureCreation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CreatureCreation query()
 * @method static \Illuminate\Database\Eloquent\Builder|CreatureCreation whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreatureCreation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreatureCreation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreatureCreation whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreatureCreation whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreatureCreation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CreatureCreation extends Model
{
    protected $table = 'creature_creation';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}