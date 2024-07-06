<?php

namespace App\Models\Boss;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Boss\KalphiteQueen
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $kalphite_princess
 * @property int $kq_head
 * @property int $jar_of_sand
 * @property int $dragon_2h_sword
 * @property int $dragon_chainbody
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|KalphiteQueen newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KalphiteQueen newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KalphiteQueen query()
 * @method static \Illuminate\Database\Eloquent\Builder|KalphiteQueen whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KalphiteQueen whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KalphiteQueen whereDragon2hSword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KalphiteQueen whereDragonChainbody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KalphiteQueen whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KalphiteQueen whereJarOfSand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KalphiteQueen whereKalphitePrincess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KalphiteQueen whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KalphiteQueen whereKqHead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KalphiteQueen whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KalphiteQueen whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KalphiteQueen whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class KalphiteQueen extends Model
{
    protected $table = 'kalphite_queen';

    protected $fillable = [
        'obtained',
        'kill_count',
        'kalphite_princess',
        'kq_head',
        'jar_of_sand',
        'dragon_2h_sword',
        'dragon_chainbody',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
