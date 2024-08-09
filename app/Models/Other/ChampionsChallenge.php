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
 * @method static \Illuminate\Database\Eloquent\Builder|ChampionsChallenge newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChampionsChallenge newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChampionsChallenge query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChampionsChallenge whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChampionsChallenge whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChampionsChallenge whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChampionsChallenge whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChampionsChallenge whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChampionsChallenge whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ChampionsChallenge extends Model
{
    protected $table = 'champions_challenge';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}