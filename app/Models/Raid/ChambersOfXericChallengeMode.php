<?php

namespace App\Raid;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Raid\ChambersOfXericChallengeMode
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXericChallengeMode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXericChallengeMode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXericChallengeMode query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXericChallengeMode whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXericChallengeMode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXericChallengeMode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXericChallengeMode whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXericChallengeMode whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXericChallengeMode whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ChambersOfXericChallengeMode extends Model
{
    protected $table = 'chambers_of_xeric_challenge_mode';

    protected $fillable = [
        'kill_count',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
