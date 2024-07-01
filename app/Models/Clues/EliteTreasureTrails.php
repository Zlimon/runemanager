<?php

namespace App\Clues;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Clues\EliteTreasureTrails
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
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
 * @property int $fury_ornament_kit
 * @property int $dragon_chainbody_ornament_kit
 * @property int $dragon_legs/skirt_ornament_kit
 * @property int $dragon_sq_shield_ornament_kit
 * @property int $dragon_full_helm_ornament_kit
 * @property int $dragon_scimitar_ornament_kit
 * @property int $light_infinity_colour_kit
 * @property int $dark_infinity_colour_kit
 * @property int $holy_wraps
 * @property int $ranger_gloves
 * @property int $rangers_tunic
 * @property int $rangers_tights
 * @property int $black_dhide_body_(g)
 * @property int $black_dhide_chaps_(g)
 * @property int $black_dhide_body_(t)
 * @property int $black_dhide_chaps_(t)
 * @property int $royal_crown
 * @property int $royal_sceptre
 * @property int $royal_gown_top
 * @property int $royal_gown_bottom
 * @property int $musketeer_hat
 * @property int $musketeer_tabard
 * @property int $musketeer_pants
 * @property int $dark_tuxedo_jacket
 * @property int $dark_trousers
 * @property int $dark_tuxedo_shoes
 * @property int $dark_tuxedo_cuffs
 * @property int $dark_bow_tie
 * @property int $light_tuxedo_jacket
 * @property int $light_trousers
 * @property int $light_tuxedo_shoes
 * @property int $light_tuxedo_cuffs
 * @property int $light_bow_tie
 * @property int $arceuus_scarf
 * @property int $hosidius_scarf
 * @property int $piscarilius_scarf
 * @property int $shayzien_scarf
 * @property int $lovakengj_scarf
 * @property int $bronze_dragon_mask
 * @property int $iron_dragon_mask
 * @property int $steel_dragon_mask
 * @property int $mithril_dragon_mask
 * @property int $adamant_dragon_mask
 * @property int $rune_dragon_mask
 * @property int $lava_dragon_mask
 * @property int $ring_of_nature
 * @property int $katana
 * @property int $dragon_cane
 * @property int $briefcase
 * @property int $bucket_helm
 * @property int $blacksmiths_helm
 * @property int $deerstalker
 * @property int $afro
 * @property int $big_pirate_hat
 * @property int $top_hat
 * @property int $monocle
 * @property int $sagacious_spectacles
 * @property int $fremennik_kilt
 * @property int $giant_boot
 * @property int $uris_hat
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails query()
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails where3rdAgeAmulet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails where3rdAgeBow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails where3rdAgeCloak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails where3rdAgeFullHelmet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails where3rdAgeKiteshield($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails where3rdAgeLongsword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails where3rdAgeMageHat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails where3rdAgePlatebody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails where3rdAgePlatelegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails where3rdAgePlateskirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails where3rdAgeRangeCoif($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails where3rdAgeRangeLegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails where3rdAgeRangeTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails where3rdAgeRobe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails where3rdAgeRobeTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails where3rdAgeVambraces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails where3rdAgeWand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereAdamantDragonMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereAfro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereArceuusScarf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereBigPirateHat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereBlackDhideBody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereBlackDhideBody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereBlackDhideChaps(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereBlackDhideChaps(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereBlacksmithsHelm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereBriefcase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereBronzeDragonMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereBucketHelm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereDarkBowTie($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereDarkInfinityColourKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereDarkTrousers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereDarkTuxedoCuffs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereDarkTuxedoJacket($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereDarkTuxedoShoes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereDeerstalker($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereDragonCane($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereDragonChainbodyOrnamentKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereDragonFullHelmOrnamentKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereDragonLegs/skirtOrnamentKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereDragonScimitarOrnamentKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereDragonSqShieldOrnamentKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereFremennikKilt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereFuryOrnamentKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereGiantBoot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereGilded2hSword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereGildedAxe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereGildedBoots($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereGildedChainbody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereGildedCoif($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereGildedDhideBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereGildedDhideChaps($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereGildedDhideVambraces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereGildedFullHelm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereGildedHasta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereGildedKiteshield($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereGildedMedHelm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereGildedPickaxe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereGildedPlatebody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereGildedPlatelegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereGildedPlateskirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereGildedScimitar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereGildedSpade($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereGildedSpear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereGildedSqShield($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereHolyWraps($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereHosidiusScarf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereIronDragonMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereKatana($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereLavaDragonMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereLightBowTie($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereLightInfinityColourKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereLightTrousers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereLightTuxedoCuffs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereLightTuxedoJacket($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereLightTuxedoShoes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereLovakengjScarf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereMithrilDragonMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereMonocle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereMusketeerHat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereMusketeerPants($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereMusketeerTabard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails wherePiscariliusScarf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereRangerGloves($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereRangersTights($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereRangersTunic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereRingOf3rdAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereRingOfNature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereRoyalCrown($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereRoyalGownBottom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereRoyalGownTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereRoyalSceptre($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereRuneDragonMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereSagaciousSpectacles($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereShayzienScarf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereSteelDragonMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereTopHat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereUrisHat($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereBlackDhideBody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereBlackDhideBody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereBlackDhideChaps(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereBlackDhideChaps(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereDragonLegs/skirtOrnamentKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereBlackDhideBody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereBlackDhideBody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereBlackDhideChaps(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereBlackDhideChaps(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EliteTreasureTrails whereDragonLegs/skirtOrnamentKit($value)
 */
class EliteTreasureTrails extends Model
{
    protected $table = 'elite_treasure_trails';

    protected $fillable = [
        'obtained',
        'kill_count',
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
        'fury_ornament_kit',
        'dragon_chainbody_ornament_kit',
        'dragon_legs/skirt_ornament_kit',
        'dragon_sq_shield_ornament_kit',
        'dragon_full_helm_ornament_kit',
        'dragon_scimitar_ornament_kit',
        'light_infinity_colour_kit',
        'dark_infinity_colour_kit',
        'holy_wraps',
        'ranger_gloves',
        'rangers_tunic',
        'rangers_tights',
        'black_dhide_body_(g)',
        'black_dhide_chaps_(g)',
        'black_dhide_body_(t)',
        'black_dhide_chaps_(t)',
        'royal_crown',
        'royal_sceptre',
        'royal_gown_top',
        'royal_gown_bottom',
        'musketeer_hat',
        'musketeer_tabard',
        'musketeer_pants',
        'dark_tuxedo_jacket',
        'dark_trousers',
        'dark_tuxedo_shoes',
        'dark_tuxedo_cuffs',
        'dark_bow_tie',
        'light_tuxedo_jacket',
        'light_trousers',
        'light_tuxedo_shoes',
        'light_tuxedo_cuffs',
        'light_bow_tie',
        'arceuus_scarf',
        'hosidius_scarf',
        'piscarilius_scarf',
        'shayzien_scarf',
        'lovakengj_scarf',
        'bronze_dragon_mask',
        'iron_dragon_mask',
        'steel_dragon_mask',
        'mithril_dragon_mask',
        'adamant_dragon_mask',
        'rune_dragon_mask',
        'lava_dragon_mask',
        'ring_of_nature',
        'katana',
        'dragon_cane',
        'briefcase',
        'bucket_helm',
        'blacksmiths_helm',
        'deerstalker',
        'afro',
        'big_pirate_hat',
        'top_hat',
        'monocle',
        'sagacious_spectacles',
        'fremennik_kilt',
        'giant_boot',
        'uris_hat',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
