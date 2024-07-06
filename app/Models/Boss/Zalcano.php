<?php

namespace App\Models\Boss;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Boss\Zalcano
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $smolcano
 * @property int $crystal_tool_seed
 * @property int $zalcano_shard
 * @property int $uncut_onyx
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Zalcano newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Zalcano newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Zalcano query()
 * @method static \Illuminate\Database\Eloquent\Builder|Zalcano whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zalcano whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zalcano whereCrystalToolSeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zalcano whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zalcano whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zalcano whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zalcano whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zalcano whereSmolcano($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zalcano whereUncutOnyx($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zalcano whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zalcano whereZalcanoShard($value)
 * @mixin \Eloquent
 */
class Zalcano extends Model
{
    protected $table = 'zalcano';

    protected $fillable = [
        'obtained',
        'kill_count',
        'smolcano',
        'crystal_tool_seed',
        'zalcano_shard',
        'uncut_onyx',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
