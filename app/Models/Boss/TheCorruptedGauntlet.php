<?php

namespace App\Models\Boss;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Boss\TheCorruptedGauntlet
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|TheCorruptedGauntlet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheCorruptedGauntlet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheCorruptedGauntlet query()
 * @method static \Illuminate\Database\Eloquent\Builder|TheCorruptedGauntlet whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheCorruptedGauntlet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheCorruptedGauntlet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheCorruptedGauntlet whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheCorruptedGauntlet whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheCorruptedGauntlet whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TheCorruptedGauntlet extends Model
{
    protected $table = 'the_corrupted_gauntlet';

    protected $fillable = [
        'kill_count',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
