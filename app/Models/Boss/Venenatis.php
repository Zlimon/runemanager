<?php

namespace App\Models\Boss;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Boss\Venenatis
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $venenatis_spiderling
 * @property int $treasonous_ring
 * @property int $dragon_pickaxe
 * @property int $dragon_2h_sword
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Venenatis newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Venenatis newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Venenatis query()
 * @method static \Illuminate\Database\Eloquent\Builder|Venenatis whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Venenatis whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Venenatis whereDragon2hSword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Venenatis whereDragonPickaxe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Venenatis whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Venenatis whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Venenatis whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Venenatis whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Venenatis whereTreasonousRing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Venenatis whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Venenatis whereVenenatisSpiderling($value)
 * @mixin \Eloquent
 */
class Venenatis extends Model
{
    protected $table = 'venenatis';

    protected $fillable = [
        'obtained',
        'kill_count',
        'venenatis_spiderling',
        'treasonous_ring',
        'dragon_pickaxe',
        'dragon_2h_sword',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
