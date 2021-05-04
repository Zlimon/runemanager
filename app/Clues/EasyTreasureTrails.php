<?php

namespace App\Clues;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Clues\EasyTreasureTrails
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $team_cape_zero
 * @property int $team_cape_i
 * @property int $team_cape_x
 * @property int $cape_of_skulls
 * @property int $golden_chefs_hat
 * @property int $golden_apron
 * @property int $wooden_shield_(g)
 * @property int $black_full_helm_(t)
 * @property int $black_platebody_(t)
 * @property int $black_platelegs_(t)
 * @property int $black_plateskirt_(t)
 * @property int $black_kiteshield_(t)
 * @property int $black_full_helm_(g)
 * @property int $black_platebody_(g)
 * @property int $black_platelegs_(g)
 * @property int $black_plateskirt_(g)
 * @property int $black_kiteshield_(g)
 * @property int $black_shield_(h1)
 * @property int $black_shield_(h2)
 * @property int $black_shield_(h3)
 * @property int $black_shield_(h4)
 * @property int $black_shield_(h5)
 * @property int $black_helm_(h1)
 * @property int $black_helm_(h2)
 * @property int $black_helm_(h3)
 * @property int $black_helm_(h4)
 * @property int $black_helm_(h5)
 * @property int $black_platebody_(h1)
 * @property int $black_platebody_(h2)
 * @property int $black_platebody_(h3)
 * @property int $black_platebody_(h4)
 * @property int $black_platebody_(h5)
 * @property int $steel_full_helm_(t)
 * @property int $steel_platebody_(t)
 * @property int $steel_platelegs_(t)
 * @property int $steel_plateskirt_(t)
 * @property int $steel_kiteshield_(t)
 * @property int $steel_full_helm_(g)
 * @property int $steel_platebody_(g)
 * @property int $steel_platelegs_(g)
 * @property int $steel_plateskirt_(g)
 * @property int $steel_kiteshield_(g)
 * @property int $iron_platebody_(t)
 * @property int $iron_platelegs_(t)
 * @property int $iron_plateskirt_(t)
 * @property int $iron_kiteshield_(t)
 * @property int $iron_full_helm_(t)
 * @property int $iron_platebody_(g)
 * @property int $iron_platelegs_(g)
 * @property int $iron_plateskirt_(g)
 * @property int $iron_kiteshield_(g)
 * @property int $iron_full_helm_(g)
 * @property int $bronze_platebody_(t)
 * @property int $bronze_platelegs_(t)
 * @property int $bronze_plateskirt_(t)
 * @property int $bronze_kiteshield_(t)
 * @property int $bronze_full_helm_(t)
 * @property int $bronze_platebody_(g)
 * @property int $bronze_platelegs_(g)
 * @property int $bronze_plateskirt_(g)
 * @property int $bronze_kiteshield_(g)
 * @property int $bronze_full_helm_(g)
 * @property int $studded_body_(g)
 * @property int $studded_chaps_(g)
 * @property int $studded_body_(t)
 * @property int $studded_chaps_(t)
 * @property int $leather_body_(g)
 * @property int $leather_chaps_(g)
 * @property int $blue_wizard_hat_(g)
 * @property int $blue_wizard_robe_(g)
 * @property int $blue_skirt_(g)
 * @property int $blue_wizard_hat_(t)
 * @property int $blue_wizard_robe_(t)
 * @property int $blue_skirt_(t)
 * @property int $black_wizard_hat_(g)
 * @property int $black_wizard_robe_(g)
 * @property int $black_skirt_(g)
 * @property int $black_wizard_hat_(t)
 * @property int $black_wizard_robe_(t)
 * @property int $black_skirt_(t)
 * @property int $monks_robe_top_(g)
 * @property int $monks_robe_(g)
 * @property int $saradomin_robe_top
 * @property int $saradomin_robe_legs
 * @property int $guthix_robe_top
 * @property int $guthix_robe_legs
 * @property int $zamorak_robe_top
 * @property int $zamorak_robe_legs
 * @property int $ancient_robe_top
 * @property int $ancient_robe_legs
 * @property int $armadyl_robe_top
 * @property int $armadyl_robe_legs
 * @property int $bandos_robe_top
 * @property int $bandos_robe_legs
 * @property int $bobs_red_shirt
 * @property int $bobs_green_shirt
 * @property int $bobs_blue_shirt
 * @property int $bobs_black_shirt
 * @property int $bobs_purple_shirt
 * @property int $highwayman_mask
 * @property int $blue_beret
 * @property int $black_beret
 * @property int $white_beret
 * @property int $red_beret
 * @property int $a_powdered_wig
 * @property int $beanie
 * @property int $imp_mask
 * @property int $goblin_mask
 * @property int $sleeping_cap
 * @property int $flared_trousers
 * @property int $pantaloons
 * @property int $black_cane
 * @property int $staff_of_bob_the_cat
 * @property int $red_elegant_shirt
 * @property int $red_elegant_blouse
 * @property int $red_elegant_legs
 * @property int $red_elegant_skirt
 * @property int $green_elegant_shirt
 * @property int $green_elegant_blouse
 * @property int $green_elegant_legs
 * @property int $green_elegant_skirt
 * @property int $blue_elegant_shirt
 * @property int $blue_elegant_blouse
 * @property int $blue_elegant_legs
 * @property int $blue_elegant_skirt
 * @property int $amulet_of_magic_(t)
 * @property int $amulet_of_power_(t)
 * @property int $black_pickaxe
 * @property int $ham_joint
 * @property int $rain_bow
 * @property int $willow_comp_bow
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails query()
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereAPowderedWig($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereAmuletOfMagic(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereAmuletOfPower(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereAncientRobeLegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereAncientRobeTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereArmadylRobeLegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereArmadylRobeTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBandosRobeLegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBandosRobeTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBeanie($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackBeret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackCane($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackFullHelm(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackFullHelm(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackHelm(h1)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackHelm(h2)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackHelm(h3)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackHelm(h4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackHelm(h5)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackKiteshield(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackKiteshield(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackPickaxe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackPlatebody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackPlatebody(h1)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackPlatebody(h2)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackPlatebody(h3)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackPlatebody(h4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackPlatebody(h5)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackPlatebody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackPlatelegs(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackPlatelegs(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackPlateskirt(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackPlateskirt(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackShield(h1)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackShield(h2)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackShield(h3)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackShield(h4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackShield(h5)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackSkirt(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackSkirt(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackWizardHat(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackWizardHat(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackWizardRobe(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlackWizardRobe(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlueBeret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlueElegantBlouse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlueElegantLegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlueElegantShirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlueElegantSkirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlueSkirt(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlueSkirt(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlueWizardHat(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlueWizardHat(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlueWizardRobe(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBlueWizardRobe(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBobsBlackShirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBobsBlueShirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBobsGreenShirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBobsPurpleShirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBobsRedShirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBronzeFullHelm(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBronzeFullHelm(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBronzeKiteshield(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBronzeKiteshield(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBronzePlatebody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBronzePlatebody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBronzePlatelegs(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBronzePlatelegs(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBronzePlateskirt(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereBronzePlateskirt(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereCapeOfSkulls($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereFlaredTrousers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereGoblinMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereGoldenApron($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereGoldenChefsHat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereGreenElegantBlouse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereGreenElegantLegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereGreenElegantShirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereGreenElegantSkirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereGuthixRobeLegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereGuthixRobeTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereHamJoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereHighwaymanMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereImpMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereIronFullHelm(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereIronFullHelm(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereIronKiteshield(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereIronKiteshield(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereIronPlatebody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereIronPlatebody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereIronPlatelegs(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereIronPlatelegs(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereIronPlateskirt(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereIronPlateskirt(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereLeatherBody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereLeatherChaps(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereMonksRobe(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereMonksRobeTop(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails wherePantaloons($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereRainBow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereRedBeret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereRedElegantBlouse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereRedElegantLegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereRedElegantShirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereRedElegantSkirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereSaradominRobeLegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereSaradominRobeTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereSleepingCap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereStaffOfBobTheCat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereSteelFullHelm(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereSteelFullHelm(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereSteelKiteshield(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereSteelKiteshield(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereSteelPlatebody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereSteelPlatebody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereSteelPlatelegs(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereSteelPlatelegs(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereSteelPlateskirt(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereSteelPlateskirt(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereStuddedBody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereStuddedBody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereStuddedChaps(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereStuddedChaps(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereTeamCapeI($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereTeamCapeX($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereTeamCapeZero($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereWhiteBeret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereWillowCompBow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereWoodenShield(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereZamorakRobeLegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EasyTreasureTrails whereZamorakRobeTop($value)
 * @mixin \Eloquent
 */
class EasyTreasureTrails extends Model
{
    protected $table = 'easy_treasure_trails';

    protected $fillable = [
        'obtained',
        'kill_count',
        'team_cape_zero',
        'team_cape_i',
        'team_cape_x',
        'cape_of_skulls',
        'golden_chefs_hat',
        'golden_apron',
        'wooden_shield_(g)',
        'black_full_helm_(t)',
        'black_platebody_(t)',
        'black_platelegs_(t)',
        'black_plateskirt_(t)',
        'black_kiteshield_(t)',
        'black_full_helm_(g)',
        'black_platebody_(g)',
        'black_platelegs_(g)',
        'black_plateskirt_(g)',
        'black_kiteshield_(g)',
        'black_shield_(h1)',
        'black_shield_(h2)',
        'black_shield_(h3)',
        'black_shield_(h4)',
        'black_shield_(h5)',
        'black_helm_(h1)',
        'black_helm_(h2)',
        'black_helm_(h3)',
        'black_helm_(h4)',
        'black_helm_(h5)',
        'black_platebody_(h1)',
        'black_platebody_(h2)',
        'black_platebody_(h3)',
        'black_platebody_(h4)',
        'black_platebody_(h5)',
        'steel_full_helm_(t)',
        'steel_platebody_(t)',
        'steel_platelegs_(t)',
        'steel_plateskirt_(t)',
        'steel_kiteshield_(t)',
        'steel_full_helm_(g)',
        'steel_platebody_(g)',
        'steel_platelegs_(g)',
        'steel_plateskirt_(g)',
        'steel_kiteshield_(g)',
        'iron_platebody_(t)',
        'iron_platelegs_(t)',
        'iron_plateskirt_(t)',
        'iron_kiteshield_(t)',
        'iron_full_helm_(t)',
        'iron_platebody_(g)',
        'iron_platelegs_(g)',
        'iron_plateskirt_(g)',
        'iron_kiteshield_(g)',
        'iron_full_helm_(g)',
        'bronze_platebody_(t)',
        'bronze_platelegs_(t)',
        'bronze_plateskirt_(t)',
        'bronze_kiteshield_(t)',
        'bronze_full_helm_(t)',
        'bronze_platebody_(g)',
        'bronze_platelegs_(g)',
        'bronze_plateskirt_(g)',
        'bronze_kiteshield_(g)',
        'bronze_full_helm_(g)',
        'studded_body_(g)',
        'studded_chaps_(g)',
        'studded_body_(t)',
        'studded_chaps_(t)',
        'leather_body_(g)',
        'leather_chaps_(g)',
        'blue_wizard_hat_(g)',
        'blue_wizard_robe_(g)',
        'blue_skirt_(g)',
        'blue_wizard_hat_(t)',
        'blue_wizard_robe_(t)',
        'blue_skirt_(t)',
        'black_wizard_hat_(g)',
        'black_wizard_robe_(g)',
        'black_skirt_(g)',
        'black_wizard_hat_(t)',
        'black_wizard_robe_(t)',
        'black_skirt_(t)',
        'monks_robe_top_(g)',
        'monks_robe_(g)',
        'saradomin_robe_top',
        'saradomin_robe_legs',
        'guthix_robe_top',
        'guthix_robe_legs',
        'zamorak_robe_top',
        'zamorak_robe_legs',
        'ancient_robe_top',
        'ancient_robe_legs',
        'armadyl_robe_top',
        'armadyl_robe_legs',
        'bandos_robe_top',
        'bandos_robe_legs',
        'bobs_red_shirt',
        'bobs_green_shirt',
        'bobs_blue_shirt',
        'bobs_black_shirt',
        'bobs_purple_shirt',
        'highwayman_mask',
        'blue_beret',
        'black_beret',
        'white_beret',
        'red_beret',
        'a_powdered_wig',
        'beanie',
        'imp_mask',
        'goblin_mask',
        'sleeping_cap',
        'flared_trousers',
        'pantaloons',
        'black_cane',
        'staff_of_bob_the_cat',
        'red_elegant_shirt',
        'red_elegant_blouse',
        'red_elegant_legs',
        'red_elegant_skirt',
        'green_elegant_shirt',
        'green_elegant_blouse',
        'green_elegant_legs',
        'green_elegant_skirt',
        'blue_elegant_shirt',
        'blue_elegant_blouse',
        'blue_elegant_legs',
        'blue_elegant_skirt',
        'amulet_of_magic_(t)',
        'amulet_of_power_(t)',
        'black_pickaxe',
        'ham_joint',
        'rain_bow',
        'willow_comp_bow',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
