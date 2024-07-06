<?php

namespace App\Models\Boss;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Boss\Nightmare
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $little_nightmare
 * @property int $inquisitors_mace
 * @property int $inquisitors_great_helm
 * @property int $inquisitors_hauberk
 * @property int $inquisitors_plateskirt
 * @property int $nightmare_staff
 * @property int $volatile_orb
 * @property int $harmonised_orb
 * @property int $eldritch_orb
 * @property int $jar_of_dreams
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare query()
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare whereEldritchOrb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare whereHarmonisedOrb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare whereInquisitorsGreatHelm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare whereInquisitorsHauberk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare whereInquisitorsMace($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare whereInquisitorsPlateskirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare whereJarOfDreams($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare whereLittleNightmare($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare whereNightmareStaff($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare whereVolatileOrb($value)
 * @mixin \Eloquent
 */
class Nightmare extends Model
{
    protected $table = 'nightmare';

    protected $fillable = [
        'obtained',
        'kill_count',
        'little_nightmare',
        'inquisitors_mace',
        'inquisitors_great_helm',
        'inquisitors_hauberk',
        'inquisitors_plateskirt',
        'nightmare_staff',
        'volatile_orb',
        'harmonised_orb',
        'eldritch_orb',
        'jar_of_dreams',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
