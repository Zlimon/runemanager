<?php

namespace App\Models\Raid;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Raid\TheatreOfBlood
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $lil_zik
 * @property int $scythe_of_vitur_(uncharged)
 * @property int $ghrazi_rapier
 * @property int $sanguinesti_staff_(uncharged)
 * @property int $justiciar_faceguard
 * @property int $justiciar_chestguard
 * @property int $justiciar_legguards
 * @property int $avernic_defender_hilt
 * @property int $vial_of_blood
 * @property int $sinhaza_shroud_tier_1
 * @property int $sinhaza_shroud_tier_2
 * @property int $sinhaza_shroud_tier_3
 * @property int $sinhaza_shroud_tier_4
 * @property int $sinhaza_shroud_tier_5
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood query()
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereAvernicDefenderHilt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereGhraziRapier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereJusticiarChestguard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereJusticiarFaceguard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereJusticiarLegguards($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereLilZik($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereSanguinestiStaff(uncharged)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereScytheOfVitur(uncharged)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereSinhazaShroudTier1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereSinhazaShroudTier2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereSinhazaShroudTier3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereSinhazaShroudTier4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereSinhazaShroudTier5($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereVialOfBlood($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereSanguinestiStaff(uncharged)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereScytheOfVitur(uncharged)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereSanguinestiStaff(uncharged)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereScytheOfVitur(uncharged)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereSanguinestiStaff(uncharged)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereScytheOfVitur(uncharged)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereSanguinestiStaff(uncharged)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereScytheOfVitur(uncharged)($value)
 * @mixin \Eloquent
 */
class TheatreOfBlood extends Model
{
    protected $table = 'theatre_of_blood';

    protected $fillable = [
        'obtained',
        'kill_count',
        'lil_zik',
        'scythe_of_vitur_(uncharged)',
        'ghrazi_rapier',
        'sanguinesti_staff_(uncharged)',
        'justiciar_faceguard',
        'justiciar_chestguard',
        'justiciar_legguards',
        'avernic_defender_hilt',
        'vial_of_blood',
        'sinhaza_shroud_tier_1',
        'sinhaza_shroud_tier_2',
        'sinhaza_shroud_tier_3',
        'sinhaza_shroud_tier_4',
        'sinhaza_shroud_tier_5',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
