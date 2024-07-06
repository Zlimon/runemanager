<?php

namespace App\Models\Boss;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Boss\PhosanisNightmare
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
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare query()
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare whereEldritchOrb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare whereHarmonisedOrb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare whereInquisitorsGreatHelm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare whereInquisitorsHauberk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare whereInquisitorsMace($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare whereInquisitorsPlateskirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare whereJarOfDreams($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare whereLittleNightmare($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare whereNightmareStaff($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare whereVolatileOrb($value)
 * @mixin \Eloquent
 */
class PhosanisNightmare extends Model
{
    protected $table = 'phosanis_nightmare';

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
