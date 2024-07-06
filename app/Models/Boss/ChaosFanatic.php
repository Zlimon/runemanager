<?php

namespace App\Models\Boss;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Boss\ChaosFanatic
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $pet_chaos_elemental
 * @property int $odium_shard_1
 * @property int $malediction_shard_1
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosFanatic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosFanatic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosFanatic query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosFanatic whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosFanatic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosFanatic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosFanatic whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosFanatic whereMaledictionShard1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosFanatic whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosFanatic whereOdiumShard1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosFanatic wherePetChaosElemental($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosFanatic whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosFanatic whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ChaosFanatic extends Model
{
    protected $table = 'chaos_fanatic';

    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_chaos_elemental',
        'odium_shard_1',
        'malediction_shard_1',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
