<?php

namespace App\Models\Boss;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Boss\Skotizo
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $skotos
 * @property int $jar_of_darkness
 * @property int $dark_claw
 * @property int $dark_totem
 * @property int $uncut_onyx
 * @property int $ancient_shard
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo query()
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo whereAncientShard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo whereDarkClaw($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo whereDarkTotem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo whereJarOfDarkness($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo whereSkotos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo whereUncutOnyx($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Skotizo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Skotizo extends Model
{
    protected $table = 'skotizo';

    protected $fillable = [
        'obtained',
        'kill_count',
        'skotos',
        'jar_of_darkness',
        'dark_claw',
        'dark_totem',
        'uncut_onyx',
        'ancient_shard',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
