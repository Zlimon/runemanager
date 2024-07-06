<?php

namespace App\Models\Raid;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Raid\ChambersOfXeric
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $olmlet
 * @property int $metamorphic_dust
 * @property int $twisted_bow
 * @property int $elder_maul
 * @property int $kodai_insignia
 * @property int $dragon_claws
 * @property int $ancestral_hat
 * @property int $ancestral_robe_top
 * @property int $ancestral_robe_bottom
 * @property int $dinhs_bulwark
 * @property int $dexterous_prayer_scroll
 * @property int $arcane_prayer_scroll
 * @property int $dragon_hunter_crossbow
 * @property int $twisted_buckler
 * @property int $torn_prayer_scroll
 * @property int $dark_relic
 * @property int $onyx
 * @property int $twisted_ancestral_colour_kit
 * @property int $xerics_guard
 * @property int $xerics_warrior
 * @property int $xerics_sentinel
 * @property int $xerics_general
 * @property int $xerics_champion
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereAncestralHat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereAncestralRobeBottom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereAncestralRobeTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereArcanePrayerScroll($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereDarkRelic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereDexterousPrayerScroll($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereDinhsBulwark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereDragonClaws($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereDragonHunterCrossbow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereElderMaul($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereKodaiInsignia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereMetamorphicDust($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereOlmlet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereOnyx($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereTornPrayerScroll($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereTwistedAncestralColourKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereTwistedBow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereTwistedBuckler($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereXericsChampion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereXericsGeneral($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereXericsGuard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereXericsSentinel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereXericsWarrior($value)
 * @mixin \Eloquent
 */
class ChambersOfXeric extends Model
{
    protected $table = 'chambers_of_xeric';

    protected $fillable = [
        'obtained',
        'kill_count',
        'olmlet',
        'metamorphic_dust',
        'twisted_bow',
        'elder_maul',
        'kodai_insignia',
        'dragon_claws',
        'ancestral_hat',
        'ancestral_robe_top',
        'ancestral_robe_bottom',
        'dinhs_bulwark',
        'dexterous_prayer_scroll',
        'arcane_prayer_scroll',
        'dragon_hunter_crossbow',
        'twisted_buckler',
        'torn_prayer_scroll',
        'dark_relic',
        'onyx',
        'twisted_ancestral_colour_kit',
        'xerics_guard',
        'xerics_warrior',
        'xerics_sentinel',
        'xerics_general',
        'xerics_champion',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
