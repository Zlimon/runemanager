<?php

namespace App\Models\Clue;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Clues\MasterTreasureTrails
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $bloodhound
 * @property int $3rd_age_pickaxe
 * @property int $3rd_age_axe
 * @property int $3rd_age_longsword
 * @property int $3rd_age_wand
 * @property int $3rd_age_cloak
 * @property int $3rd_age_bow
 * @property int $3rd_age_range_coif
 * @property int $3rd_age_range_top
 * @property int $3rd_age_range_legs
 * @property int $3rd_age_vambraces
 * @property int $3rd_age_robe_top
 * @property int $3rd_age_robe
 * @property int $3rd_age_mage_hat
 * @property int $3rd_age_amulet
 * @property int $3rd_age_plateskirt
 * @property int $3rd_age_platelegs
 * @property int $3rd_age_platebody
 * @property int $3rd_age_full_helmet
 * @property int $3rd_age_kiteshield
 * @property int $3rd_age_druidic_robe_bottoms
 * @property int $3rd_age_druidic_robe_top
 * @property int $3rd_age_druidic_staff
 * @property int $3rd_age_druidic_cloak
 * @property int $ring_of_3rd_age
 * @property int $gilded_scimitar
 * @property int $gilded_boots
 * @property int $gilded_platebody
 * @property int $gilded_platelegs
 * @property int $gilded_plateskirt
 * @property int $gilded_full_helm
 * @property int $gilded_kiteshield
 * @property int $gilded_med_helm
 * @property int $gilded_chainbody
 * @property int $gilded_sq_shield
 * @property int $gilded_2h_sword
 * @property int $gilded_spear
 * @property int $gilded_hasta
 * @property int $gilded_coif
 * @property int $gilded_dhide_vambraces
 * @property int $gilded_dhide_body
 * @property int $gilded_dhide_chaps
 * @property int $gilded_pickaxe
 * @property int $gilded_axe
 * @property int $gilded_spade
 * @property int $bucket_helm_(g)
 * @property int $ring_of_coins
 * @property int $armadyl_godsword_ornament_kit
 * @property int $bandos_godsword_ornament_kit
 * @property int $saradomin_godsword_ornament_kit
 * @property int $zamorak_godsword_ornament_kit
 * @property int $occult_ornament_kit
 * @property int $torture_ornament_kit
 * @property int $anguish_ornament_kit
 * @property int $dragon_defender_ornament_kit
 * @property int $dragon_kiteshield_ornament_kit
 * @property int $dragon_platebody_ornament_kit
 * @property int $tormented_ornament_kit
 * @property int $hood_of_darkness
 * @property int $robe_top_of_darkness
 * @property int $robe_bottom_of_darkness
 * @property int $gloves_of_darkness
 * @property int $boots_of_darkness
 * @property int $samurai_kasa
 * @property int $samurai_shirt
 * @property int $samurai_greaves
 * @property int $samurai_boots
 * @property int $samurai_gloves
 * @property int $ankou_mask
 * @property int $ankou_top
 * @property int $ankou_gloves
 * @property int $ankou_socks
 * @property int $ankous_leggings
 * @property int $mummys_head
 * @property int $mummys_feet
 * @property int $mummys_hands
 * @property int $mummys_legs
 * @property int $mummys_body
 * @property int $shayzien_hood
 * @property int $hosidius_hood
 * @property int $arceuus_hood
 * @property int $piscarilius_hood
 * @property int $lovakengj_hood
 * @property int $lesser_demon_mask
 * @property int $greater_demon_mask
 * @property int $black_demon_mask
 * @property int $jungle_demon_mask
 * @property int $old_demon_mask
 * @property int $left_eye_patch
 * @property int $bowl_wig
 * @property int $ale_of_the_gods
 * @property int $obsidian_cape_(r)
 * @property int $half_moon_spectacles
 * @property int $fancy_tiara
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails query()
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails where3rdAgeAmulet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails where3rdAgeAxe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails where3rdAgeBow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails where3rdAgeCloak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails where3rdAgeDruidicCloak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails where3rdAgeDruidicRobeBottoms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails where3rdAgeDruidicRobeTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails where3rdAgeDruidicStaff($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails where3rdAgeFullHelmet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails where3rdAgeKiteshield($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails where3rdAgeLongsword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails where3rdAgeMageHat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails where3rdAgePickaxe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails where3rdAgePlatebody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails where3rdAgePlatelegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails where3rdAgePlateskirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails where3rdAgeRangeCoif($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails where3rdAgeRangeLegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails where3rdAgeRangeTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails where3rdAgeRobe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails where3rdAgeRobeTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails where3rdAgeVambraces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails where3rdAgeWand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereAleOfTheGods($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereAnguishOrnamentKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereAnkouGloves($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereAnkouMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereAnkouSocks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereAnkouTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereAnkousLeggings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereArceuusHood($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereArmadylGodswordOrnamentKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereBandosGodswordOrnamentKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereBlackDemonMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereBloodhound($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereBootsOfDarkness($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereBowlWig($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereBucketHelm(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereDragonDefenderOrnamentKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereDragonKiteshieldOrnamentKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereDragonPlatebodyOrnamentKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereFancyTiara($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereGilded2hSword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereGildedAxe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereGildedBoots($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereGildedChainbody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereGildedCoif($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereGildedDhideBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereGildedDhideChaps($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereGildedDhideVambraces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereGildedFullHelm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereGildedHasta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereGildedKiteshield($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereGildedMedHelm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereGildedPickaxe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereGildedPlatebody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereGildedPlatelegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereGildedPlateskirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereGildedScimitar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereGildedSpade($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereGildedSpear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereGildedSqShield($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereGlovesOfDarkness($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereGreaterDemonMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereHalfMoonSpectacles($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereHoodOfDarkness($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereHosidiusHood($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereJungleDemonMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereLeftEyePatch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereLesserDemonMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereLovakengjHood($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereMummysBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereMummysFeet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereMummysHands($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereMummysHead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereMummysLegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereObsidianCape(r)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereOccultOrnamentKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereOldDemonMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails wherePiscariliusHood($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereRingOf3rdAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereRingOfCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereRobeBottomOfDarkness($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereRobeTopOfDarkness($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereSamuraiBoots($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereSamuraiGloves($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereSamuraiGreaves($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereSamuraiKasa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereSamuraiShirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereSaradominGodswordOrnamentKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereShayzienHood($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereTormentedOrnamentKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereTortureOrnamentKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereZamorakGodswordOrnamentKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereBucketHelm(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereObsidianCape(r)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereBucketHelm(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereObsidianCape(r)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereBucketHelm(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereObsidianCape(r)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereBucketHelm(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MasterTreasureTrails whereObsidianCape(r)($value)
 * @mixin \Eloquent
 */
class MasterTreasureTrails extends Model
{
    protected $table = 'master_treasure_trails';

    protected $fillable = [
        'obtained',
        'kill_count',
        'bloodhound',
        '3rd_age_pickaxe',
        '3rd_age_axe',
        '3rd_age_longsword',
        '3rd_age_wand',
        '3rd_age_cloak',
        '3rd_age_bow',
        '3rd_age_range_coif',
        '3rd_age_range_top',
        '3rd_age_range_legs',
        '3rd_age_vambraces',
        '3rd_age_robe_top',
        '3rd_age_robe',
        '3rd_age_mage_hat',
        '3rd_age_amulet',
        '3rd_age_plateskirt',
        '3rd_age_platelegs',
        '3rd_age_platebody',
        '3rd_age_full_helmet',
        '3rd_age_kiteshield',
        '3rd_age_druidic_robe_bottoms',
        '3rd_age_druidic_robe_top',
        '3rd_age_druidic_staff',
        '3rd_age_druidic_cloak',
        'ring_of_3rd_age',
        'gilded_scimitar',
        'gilded_boots',
        'gilded_platebody',
        'gilded_platelegs',
        'gilded_plateskirt',
        'gilded_full_helm',
        'gilded_kiteshield',
        'gilded_med_helm',
        'gilded_chainbody',
        'gilded_sq_shield',
        'gilded_2h_sword',
        'gilded_spear',
        'gilded_hasta',
        'gilded_coif',
        'gilded_dhide_vambraces',
        'gilded_dhide_body',
        'gilded_dhide_chaps',
        'gilded_pickaxe',
        'gilded_axe',
        'gilded_spade',
        'bucket_helm_(g)',
        'ring_of_coins',
        'armadyl_godsword_ornament_kit',
        'bandos_godsword_ornament_kit',
        'saradomin_godsword_ornament_kit',
        'zamorak_godsword_ornament_kit',
        'occult_ornament_kit',
        'torture_ornament_kit',
        'anguish_ornament_kit',
        'dragon_defender_ornament_kit',
        'dragon_kiteshield_ornament_kit',
        'dragon_platebody_ornament_kit',
        'tormented_ornament_kit',
        'hood_of_darkness',
        'robe_top_of_darkness',
        'robe_bottom_of_darkness',
        'gloves_of_darkness',
        'boots_of_darkness',
        'samurai_kasa',
        'samurai_shirt',
        'samurai_greaves',
        'samurai_boots',
        'samurai_gloves',
        'ankou_mask',
        'ankou_top',
        'ankou_gloves',
        'ankou_socks',
        'ankous_leggings',
        'mummys_head',
        'mummys_feet',
        'mummys_hands',
        'mummys_legs',
        'mummys_body',
        'shayzien_hood',
        'hosidius_hood',
        'arceuus_hood',
        'piscarilius_hood',
        'lovakengj_hood',
        'lesser_demon_mask',
        'greater_demon_mask',
        'black_demon_mask',
        'jungle_demon_mask',
        'old_demon_mask',
        'left_eye_patch',
        'bowl_wig',
        'ale_of_the_gods',
        'obsidian_cape_(r)',
        'half_moon_spectacles',
        'fancy_tiara',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
