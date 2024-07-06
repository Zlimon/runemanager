<?php

namespace App\Models\Boss;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Boss\Zulrah
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $pet_snakeling
 * @property int $tanzanite_mutagen
 * @property int $magma_mutagen
 * @property int $jar_of_swamp
 * @property int $magic_fang
 * @property int $serpentine_visage
 * @property int $tanzanite_fang
 * @property int $zul-andra_teleport
 * @property int $uncut_onyx
 * @property int $zulrahs_scales
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah query()
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah whereJarOfSwamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah whereMagicFang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah whereMagmaMutagen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah wherePetSnakeling($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah whereSerpentineVisage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah whereTanzaniteFang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah whereTanzaniteMutagen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah whereUncutOnyx($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah whereZulAndraTeleport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zulrah whereZulrahsScales($value)
 * @mixin \Eloquent
 */
class Zulrah extends Model
{
    protected $table = 'zulrah';

    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_snakeling',
        'tanzanite_mutagen',
        'magma_mutagen',
        'jar_of_swamp',
        'magic_fang',
        'serpentine_visage',
        'tanzanite_fang',
        'zul-andra_teleport',
        'uncut_onyx',
        'zulrahs_scales',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
