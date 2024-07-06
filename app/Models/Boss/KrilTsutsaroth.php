<?php

namespace App\Models\Boss;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Boss\KrilTsutsaroth
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $pet_kril_tsutsaroth
 * @property int $staff_of_the_dead
 * @property int $zamorakian_spear
 * @property int $steam_battlestaff
 * @property int $zamorak_hilt
 * @property int $godsword_shard_1
 * @property int $godsword_shard_2
 * @property int $godsword_shard_3
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth query()
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth whereGodswordShard1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth whereGodswordShard2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth whereGodswordShard3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth wherePetKrilTsutsaroth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth whereStaffOfTheDead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth whereSteamBattlestaff($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth whereZamorakHilt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KrilTsutsaroth whereZamorakianSpear($value)
 * @mixin \Eloquent
 */
class KrilTsutsaroth extends Model
{
    protected $table = 'kril_tsutsaroth';

    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_kril_tsutsaroth',
        'staff_of_the_dead',
        'zamorakian_spear',
        'steam_battlestaff',
        'zamorak_hilt',
        'godsword_shard_1',
        'godsword_shard_2',
        'godsword_shard_3',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
