<?php

namespace App\Models\Boss;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Boss\Hespori
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $bottomless_compost_bucket
 * @property int $iasor_seed
 * @property int $kronos_seed
 * @property int $attas_seed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Hespori newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Hespori newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Hespori query()
 * @method static \Illuminate\Database\Eloquent\Builder|Hespori whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hespori whereAttasSeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hespori whereBottomlessCompostBucket($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hespori whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hespori whereIasorSeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hespori whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hespori whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hespori whereKronosSeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hespori whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hespori whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hespori whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Hespori extends Model
{
    protected $table = 'hespori';

    protected $fillable = [
        'obtained',
        'kill_count',
        'bottomless_compost_bucket',
        'iasor_seed',
        'kronos_seed',
        'attas_seed',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
