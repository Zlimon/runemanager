<?php

namespace App\Models\Boss;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Boss\GiantMole
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $baby_mole
 * @property int $mole_skin
 * @property int $mole_claw
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|GiantMole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GiantMole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GiantMole query()
 * @method static \Illuminate\Database\Eloquent\Builder|GiantMole whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiantMole whereBabyMole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiantMole whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiantMole whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiantMole whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiantMole whereMoleClaw($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiantMole whereMoleSkin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiantMole whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiantMole whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiantMole whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GiantMole extends Model
{
    protected $table = 'giant_mole';

    protected $fillable = [
        'obtained',
        'kill_count',
        'baby_mole',
        'mole_skin',
        'mole_claw',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
