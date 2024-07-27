<?php

namespace App\Models\Clue;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Clues\HardTreasureTrails
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
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
 * @property int $robin_hood_hat
 * @property int $dragon_boots_ornament_kit
 * @property int $rune_defender_ornament_kit
 * @property int $tzhaar-ket-om_ornament_kit
 * @property int $berserker_necklace_ornament_kit
 * @property int $rune_full_helm_(t)
 * @property int $rune_platebody_(t)
 * @property int $rune_platelegs_(t)
 * @property int $rune_plateskirt_(t)
 * @property int $rune_kiteshield_(t)
 * @property int $rune_full_helm_(g)
 * @property int $rune_platebody_(g)
 * @property int $rune_platelegs_(g)
 * @property int $rune_plateskirt_(g)
 * @property int $rune_kiteshield_(g)
 * @property int $zamorak_full_helm
 * @property int $zamorak_platebody
 * @property int $zamorak_platelegs
 * @property int $zamorak_plateskirt
 * @property int $zamorak_kiteshield
 * @property int $guthix_full_helm
 * @property int $guthix_platebody
 * @property int $guthix_platelegs
 * @property int $guthix_plateskirt
 * @property int $guthix_kiteshield
 * @property int $saradomin_full_helm
 * @property int $saradomin_platebody
 * @property int $saradomin_platelegs
 * @property int $saradomin_plateskirt
 * @property int $saradomin_kiteshield
 * @property int $ancient_full_helm
 * @property int $ancient_platebody
 * @property int $ancient_platelegs
 * @property int $ancient_plateskirt
 * @property int $ancient_kiteshield
 * @property int $armadyl_full_helm
 * @property int $armadyl_platebody
 * @property int $armadyl_platelegs
 * @property int $armadyl_plateskirt
 * @property int $armadyl_kiteshield
 * @property int $bandos_full_helm
 * @property int $bandos_platebody
 * @property int $bandos_platelegs
 * @property int $bandos_plateskirt
 * @property int $bandos_kiteshield
 * @property int $rune_shield_(h1)
 * @property int $rune_shield_(h2)
 * @property int $rune_shield_(h3)
 * @property int $rune_shield_(h4)
 * @property int $rune_shield_(h5)
 * @property int $rune_helm_(h1)
 * @property int $rune_helm_(h2)
 * @property int $rune_helm_(h3)
 * @property int $rune_helm_(h4)
 * @property int $rune_helm_(h5)
 * @property int $rune_platebody_(h1)
 * @property int $rune_platebody_(h2)
 * @property int $rune_platebody_(h3)
 * @property int $rune_platebody_(h4)
 * @property int $rune_platebody_(h5)
 * @property int $saradomin_coif
 * @property int $saradomin_dhide_body
 * @property int $saradomin_chaps
 * @property int $saradomin_bracers
 * @property int $saradomin_dhide_boots
 * @property int $saradomin_dhide_shield
 * @property int $guthix_coif
 * @property int $guthix_dhide_body
 * @property int $guthix_chaps
 * @property int $guthix_bracers
 * @property int $guthix_dhide_boots
 * @property int $guthix_dhide_shield
 * @property int $zamorak_coif
 * @property int $zamorak_dhide_body
 * @property int $zamorak_chaps
 * @property int $zamorak_bracers
 * @property int $zamorak_dhide_boots
 * @property int $zamorak_dhide_shield
 * @property int $bandos_coif
 * @property int $bandos_dhide_body
 * @property int $bandos_chaps
 * @property int $bandos_bracers
 * @property int $bandos_dhide_boots
 * @property int $bandos_dhide_shield
 * @property int $armadyl_coif
 * @property int $armadyl_dhide_body
 * @property int $armadyl_chaps
 * @property int $armadyl_bracers
 * @property int $armadyl_dhide_boots
 * @property int $armadyl_dhide_shield
 * @property int $ancient_coif
 * @property int $ancient_dhide_body
 * @property int $ancient_chaps
 * @property int $ancient_bracers
 * @property int $ancient_dhide_boots
 * @property int $ancient_dhide_shield
 * @property int $red_dhide_body_(t)
 * @property int $red_dhide_chaps_(t)
 * @property int $red_dhide_body_(g)
 * @property int $red_dhide_chaps_(g)
 * @property int $blue_dhide_body_(t)
 * @property int $blue_dhide_chaps_(t)
 * @property int $blue_dhide_body_(g)
 * @property int $blue_dhide_chaps_(g)
 * @property int $enchanted_hat
 * @property int $enchanted_top
 * @property int $enchanted_robe
 * @property int $saradomin_stole
 * @property int $saradomin_crozier
 * @property int $guthix_stole
 * @property int $guthix_crozier
 * @property int $zamorak_stole
 * @property int $zamorak_crozier
 * @property int $zombie_head
 * @property int $cyclops_head
 * @property int $pirates_hat
 * @property int $red_cavalier
 * @property int $white_cavalier
 * @property int $navy_cavalier
 * @property int $tan_cavalier
 * @property int $dark_cavalier
 * @property int $black_cavalier
 * @property int $pith_helmet
 * @property int $explorer_backpack
 * @property int $thieving_bag
 * @property int $green_dragon_mask
 * @property int $blue_dragon_mask
 * @property int $red_dragon_mask
 * @property int $black_dragon_mask
 * @property int $nunchaku
 * @property int $dual_sai
 * @property int $rune_cane
 * @property int $amulet_of_glory_(t4)
 * @property int $magic_comp_bow
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails query()
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails where3rdAgeAmulet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails where3rdAgeFullHelmet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails where3rdAgeKiteshield($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails where3rdAgeMageHat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails where3rdAgePlatebody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails where3rdAgePlatelegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails where3rdAgePlateskirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails where3rdAgeRangeCoif($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails where3rdAgeRangeLegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails where3rdAgeRangeTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails where3rdAgeRobe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails where3rdAgeRobeTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails where3rdAgeVambraces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereAmuletOfGlory(t4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereAncientBracers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereAncientChaps($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereAncientCoif($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereAncientDhideBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereAncientDhideBoots($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereAncientDhideShield($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereAncientFullHelm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereAncientKiteshield($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereAncientPlatebody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereAncientPlatelegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereAncientPlateskirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereArmadylBracers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereArmadylChaps($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereArmadylCoif($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereArmadylDhideBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereArmadylDhideBoots($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereArmadylDhideShield($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereArmadylFullHelm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereArmadylKiteshield($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereArmadylPlatebody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereArmadylPlatelegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereArmadylPlateskirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBandosBracers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBandosChaps($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBandosCoif($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBandosDhideBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBandosDhideBoots($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBandosDhideShield($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBandosFullHelm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBandosKiteshield($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBandosPlatebody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBandosPlatelegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBandosPlateskirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBerserkerNecklaceOrnamentKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBlackCavalier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBlackDragonMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBlueDhideBody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBlueDhideBody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBlueDhideChaps(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBlueDhideChaps(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBlueDragonMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereCyclopsHead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereDarkCavalier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereDragonBootsOrnamentKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereDualSai($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereEnchantedHat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereEnchantedRobe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereEnchantedTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereExplorerBackpack($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGilded2hSword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGildedChainbody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGildedFullHelm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGildedHasta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGildedKiteshield($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGildedMedHelm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGildedPlatebody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGildedPlatelegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGildedPlateskirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGildedSpear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGildedSqShield($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGreenDragonMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGuthixBracers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGuthixChaps($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGuthixCoif($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGuthixCrozier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGuthixDhideBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGuthixDhideBoots($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGuthixDhideShield($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGuthixFullHelm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGuthixKiteshield($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGuthixPlatebody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGuthixPlatelegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGuthixPlateskirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereGuthixStole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereMagicCompBow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereNavyCavalier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereNunchaku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails wherePiratesHat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails wherePithHelmet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRedCavalier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRedDhideBody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRedDhideBody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRedDhideChaps(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRedDhideChaps(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRedDragonMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRobinHoodHat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneCane($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneDefenderOrnamentKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneFullHelm(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneFullHelm(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h1)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h2)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h3)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h5)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneKiteshield(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneKiteshield(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h1)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h2)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h3)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h5)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatelegs(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatelegs(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlateskirt(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlateskirt(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h1)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h2)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h3)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h5)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereSaradominBracers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereSaradominChaps($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereSaradominCoif($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereSaradominCrozier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereSaradominDhideBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereSaradominDhideBoots($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereSaradominDhideShield($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereSaradominFullHelm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereSaradominKiteshield($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereSaradominPlatebody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereSaradominPlatelegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereSaradominPlateskirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereSaradominStole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereTanCavalier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereThievingBag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereTzhaarKetOmOrnamentKit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereWhiteCavalier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereZamorakBracers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereZamorakChaps($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereZamorakCoif($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereZamorakCrozier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereZamorakDhideBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereZamorakDhideBoots($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereZamorakDhideShield($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereZamorakFullHelm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereZamorakKiteshield($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereZamorakPlatebody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereZamorakPlatelegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereZamorakPlateskirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereZamorakStole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereZombieHead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereAmuletOfGlory(t4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBlueDhideBody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBlueDhideBody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBlueDhideChaps(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBlueDhideChaps(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRedDhideBody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRedDhideBody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRedDhideChaps(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRedDhideChaps(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneFullHelm(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneFullHelm(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h1)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h2)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h3)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h5)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneKiteshield(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneKiteshield(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h1)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h2)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h3)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h5)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatelegs(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatelegs(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlateskirt(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlateskirt(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h1)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h2)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h3)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h5)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereAmuletOfGlory(t4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBlueDhideBody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBlueDhideBody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBlueDhideChaps(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBlueDhideChaps(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRedDhideBody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRedDhideBody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRedDhideChaps(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRedDhideChaps(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneFullHelm(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneFullHelm(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h1)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h2)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h3)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h5)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneKiteshield(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneKiteshield(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h1)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h2)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h3)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h5)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatelegs(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatelegs(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlateskirt(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlateskirt(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h1)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h2)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h3)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h5)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereAmuletOfGlory(t4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBlueDhideBody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBlueDhideBody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBlueDhideChaps(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBlueDhideChaps(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRedDhideBody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRedDhideBody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRedDhideChaps(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRedDhideChaps(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneFullHelm(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneFullHelm(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h1)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h2)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h3)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h5)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneKiteshield(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneKiteshield(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h1)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h2)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h3)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h5)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatelegs(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatelegs(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlateskirt(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlateskirt(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h1)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h2)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h3)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h5)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereAmuletOfGlory(t4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBlueDhideBody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBlueDhideBody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBlueDhideChaps(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereBlueDhideChaps(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRedDhideBody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRedDhideBody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRedDhideChaps(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRedDhideChaps(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneFullHelm(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneFullHelm(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h1)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h2)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h3)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneHelm(h5)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneKiteshield(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneKiteshield(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h1)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h2)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h3)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(h5)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatebody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatelegs(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlatelegs(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlateskirt(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRunePlateskirt(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h1)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h2)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h3)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HardTreasureTrails whereRuneShield(h5)($value)
 * @mixin \Eloquent
 */
class HardTreasureTrails extends Model
{
    protected $table = 'hard_treasure_trails';

    protected $fillable = [
        'obtained',
        'kill_count',
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
        'robin_hood_hat',
        'dragon_boots_ornament_kit',
        'rune_defender_ornament_kit',
        'tzhaar-ket-om_ornament_kit',
        'berserker_necklace_ornament_kit',
        'rune_full_helm_(t)',
        'rune_platebody_(t)',
        'rune_platelegs_(t)',
        'rune_plateskirt_(t)',
        'rune_kiteshield_(t)',
        'rune_full_helm_(g)',
        'rune_platebody_(g)',
        'rune_platelegs_(g)',
        'rune_plateskirt_(g)',
        'rune_kiteshield_(g)',
        'zamorak_full_helm',
        'zamorak_platebody',
        'zamorak_platelegs',
        'zamorak_plateskirt',
        'zamorak_kiteshield',
        'guthix_full_helm',
        'guthix_platebody',
        'guthix_platelegs',
        'guthix_plateskirt',
        'guthix_kiteshield',
        'saradomin_full_helm',
        'saradomin_platebody',
        'saradomin_platelegs',
        'saradomin_plateskirt',
        'saradomin_kiteshield',
        'ancient_full_helm',
        'ancient_platebody',
        'ancient_platelegs',
        'ancient_plateskirt',
        'ancient_kiteshield',
        'armadyl_full_helm',
        'armadyl_platebody',
        'armadyl_platelegs',
        'armadyl_plateskirt',
        'armadyl_kiteshield',
        'bandos_full_helm',
        'bandos_platebody',
        'bandos_platelegs',
        'bandos_plateskirt',
        'bandos_kiteshield',
        'rune_shield_(h1)',
        'rune_shield_(h2)',
        'rune_shield_(h3)',
        'rune_shield_(h4)',
        'rune_shield_(h5)',
        'rune_helm_(h1)',
        'rune_helm_(h2)',
        'rune_helm_(h3)',
        'rune_helm_(h4)',
        'rune_helm_(h5)',
        'rune_platebody_(h1)',
        'rune_platebody_(h2)',
        'rune_platebody_(h3)',
        'rune_platebody_(h4)',
        'rune_platebody_(h5)',
        'saradomin_coif',
        'saradomin_dhide_body',
        'saradomin_chaps',
        'saradomin_bracers',
        'saradomin_dhide_boots',
        'saradomin_dhide_shield',
        'guthix_coif',
        'guthix_dhide_body',
        'guthix_chaps',
        'guthix_bracers',
        'guthix_dhide_boots',
        'guthix_dhide_shield',
        'zamorak_coif',
        'zamorak_dhide_body',
        'zamorak_chaps',
        'zamorak_bracers',
        'zamorak_dhide_boots',
        'zamorak_dhide_shield',
        'bandos_coif',
        'bandos_dhide_body',
        'bandos_chaps',
        'bandos_bracers',
        'bandos_dhide_boots',
        'bandos_dhide_shield',
        'armadyl_coif',
        'armadyl_dhide_body',
        'armadyl_chaps',
        'armadyl_bracers',
        'armadyl_dhide_boots',
        'armadyl_dhide_shield',
        'ancient_coif',
        'ancient_dhide_body',
        'ancient_chaps',
        'ancient_bracers',
        'ancient_dhide_boots',
        'ancient_dhide_shield',
        'red_dhide_body_(t)',
        'red_dhide_chaps_(t)',
        'red_dhide_body_(g)',
        'red_dhide_chaps_(g)',
        'blue_dhide_body_(t)',
        'blue_dhide_chaps_(t)',
        'blue_dhide_body_(g)',
        'blue_dhide_chaps_(g)',
        'enchanted_hat',
        'enchanted_top',
        'enchanted_robe',
        'saradomin_stole',
        'saradomin_crozier',
        'guthix_stole',
        'guthix_crozier',
        'zamorak_stole',
        'zamorak_crozier',
        'zombie_head',
        'cyclops_head',
        'pirates_hat',
        'red_cavalier',
        'white_cavalier',
        'navy_cavalier',
        'tan_cavalier',
        'dark_cavalier',
        'black_cavalier',
        'pith_helmet',
        'explorer_backpack',
        'thieving_bag',
        'green_dragon_mask',
        'blue_dragon_mask',
        'red_dragon_mask',
        'black_dragon_mask',
        'nunchaku',
        'dual_sai',
        'rune_cane',
        'amulet_of_glory_(t4)',
        'magic_comp_bow',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
