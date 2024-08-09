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
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians query()
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrotesqueGuardians whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GrotesqueGuardians extends Model
{
    protected $table = 'grotesque_guardians';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}