<?php

namespace App\Clues;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Clues\MediumTreasureTrails
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $ranger_boots
 * @property int $wizard_boots
 * @property int $holy_sandals
 * @property int $climbing_boots_(g)
 * @property int $spiked_manacles
 * @property int $adamant_full_helm_(t)
 * @property int $adamant_platebody_(t)
 * @property int $adamant_platelegs_(t)
 * @property int $adamant_plateskirt_(t)
 * @property int $adamant_kiteshield_(t)
 * @property int $adamant_full_helm_(g)
 * @property int $adamant_platebody_(g)
 * @property int $adamant_platelegs_(g)
 * @property int $adamant_plateskirt_(g)
 * @property int $adamant_kiteshield_(g)
 * @property int $adamant_shield_(h1)
 * @property int $adamant_shield_(h2)
 * @property int $adamant_shield_(h3)
 * @property int $adamant_shield_(h4)
 * @property int $adamant_shield_(h5)
 * @property int $adamant_helm_(h1)
 * @property int $adamant_helm_(h2)
 * @property int $adamant_helm_(h3)
 * @property int $adamant_helm_(h4)
 * @property int $adamant_helm_(h5)
 * @property int $adamant_platebody_(h1)
 * @property int $adamant_platebody_(h2)
 * @property int $adamant_platebody_(h3)
 * @property int $adamant_platebody_(h4)
 * @property int $adamant_platebody_(h5)
 * @property int $mithril_full_helm_(g)
 * @property int $mithril_platebody_(g)
 * @property int $mithril_platelegs_(g)
 * @property int $mithril_plateskirt_(g)
 * @property int $mithril_kiteshield_(g)
 * @property int $mithril_full_helm_(t)
 * @property int $mithril_platebody_(t)
 * @property int $mithril_platelegs_(t)
 * @property int $mithril_plateskirt_(t)
 * @property int $mithril_kiteshield_(t)
 * @property int $green_dhide_body_(g)
 * @property int $green_dhide_body_(t)
 * @property int $green_dhide_chaps_(g)
 * @property int $green_dhide_chaps_(t)
 * @property int $saradomin_mitre
 * @property int $saradomin_cloak
 * @property int $guthix_mitre
 * @property int $guthix_cloak
 * @property int $zamorak_mitre
 * @property int $zamorak_cloak
 * @property int $ancient_mitre
 * @property int $ancient_cloak
 * @property int $ancient_stole
 * @property int $ancient_crozier
 * @property int $armadyl_mitre
 * @property int $armadyl_cloak
 * @property int $armadyl_stole
 * @property int $armadyl_crozier
 * @property int $bandos_mitre
 * @property int $bandos_cloak
 * @property int $bandos_stole
 * @property int $bandos_crozier
 * @property int $red_boater
 * @property int $green_boater
 * @property int $orange_boater
 * @property int $black_boater
 * @property int $blue_boater
 * @property int $pink_boater
 * @property int $purple_boater
 * @property int $white_boater
 * @property int $red_headband
 * @property int $black_headband
 * @property int $brown_headband
 * @property int $white_headband
 * @property int $blue_headband
 * @property int $gold_headband
 * @property int $pink_headband
 * @property int $green_headband
 * @property int $crier_hat
 * @property int $crier_coat
 * @property int $crier_bell
 * @property int $adamant_cane
 * @property int $arceuus_banner
 * @property int $piscarilius_banner
 * @property int $hosidius_banner
 * @property int $shayzien_banner
 * @property int $lovakengj_banner
 * @property int $cabbage_round_shield
 * @property int $black_unicorn_mask
 * @property int $white_unicorn_mask
 * @property int $cat_mask
 * @property int $penguin_mask
 * @property int $leprechaun_hat
 * @property int $black_leprechaun_hat
 * @property int $wolf_mask
 * @property int $wolf_cloak
 * @property int $purple_elegant_shirt
 * @property int $purple_elegant_blouse
 * @property int $purple_elegant_legs
 * @property int $purple_elegant_skirt
 * @property int $black_elegant_shirt
 * @property int $white_elegant_blouse
 * @property int $black_elegant_legs
 * @property int $white_elegant_skirt
 * @property int $pink_elegant_shirt
 * @property int $pink_elegant_blouse
 * @property int $pink_elegant_legs
 * @property int $pink_elegant_skirt
 * @property int $gold_elegant_shirt
 * @property int $gold_elegant_blouse
 * @property int $gold_elegant_legs
 * @property int $gold_elegant_skirt
 * @property int $gnomish_firelighter
 * @property int $strength_amulet_(t)
 * @property int $yew_comp_bow
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails query()
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantCane($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantFullHelm(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantFullHelm(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantHelm(h1)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantHelm(h2)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantHelm(h3)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantHelm(h4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantHelm(h5)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantKiteshield(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantKiteshield(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantPlatebody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantPlatebody(h1)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantPlatebody(h2)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantPlatebody(h3)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantPlatebody(h4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantPlatebody(h5)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantPlatebody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantPlatelegs(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantPlatelegs(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantPlateskirt(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantPlateskirt(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantShield(h1)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantShield(h2)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantShield(h3)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantShield(h4)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAdamantShield(h5)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAncientCloak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAncientCrozier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAncientMitre($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereAncientStole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereArceuusBanner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereArmadylCloak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereArmadylCrozier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereArmadylMitre($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereArmadylStole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereBandosCloak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereBandosCrozier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereBandosMitre($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereBandosStole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereBlackBoater($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereBlackElegantLegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereBlackElegantShirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereBlackHeadband($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereBlackLeprechaunHat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereBlackUnicornMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereBlueBoater($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereBlueHeadband($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereBrownHeadband($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereCabbageRoundShield($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereCatMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereClimbingBoots(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereCrierBell($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereCrierCoat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereCrierHat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereGnomishFirelighter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereGoldElegantBlouse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereGoldElegantLegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereGoldElegantShirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereGoldElegantSkirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereGoldHeadband($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereGreenBoater($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereGreenDhideBody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereGreenDhideBody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereGreenDhideChaps(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereGreenDhideChaps(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereGreenHeadband($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereGuthixCloak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereGuthixMitre($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereHolySandals($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereHosidiusBanner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereLeprechaunHat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereLovakengjBanner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereMithrilFullHelm(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereMithrilFullHelm(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereMithrilKiteshield(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereMithrilKiteshield(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereMithrilPlatebody(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereMithrilPlatebody(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereMithrilPlatelegs(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereMithrilPlatelegs(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereMithrilPlateskirt(g)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereMithrilPlateskirt(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereOrangeBoater($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails wherePenguinMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails wherePinkBoater($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails wherePinkElegantBlouse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails wherePinkElegantLegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails wherePinkElegantShirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails wherePinkElegantSkirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails wherePinkHeadband($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails wherePiscariliusBanner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails wherePurpleBoater($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails wherePurpleElegantBlouse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails wherePurpleElegantLegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails wherePurpleElegantShirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails wherePurpleElegantSkirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereRangerBoots($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereRedBoater($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereRedHeadband($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereSaradominCloak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereSaradominMitre($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereShayzienBanner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereSpikedManacles($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereStrengthAmulet(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereWhiteBoater($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereWhiteElegantBlouse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereWhiteElegantSkirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereWhiteHeadband($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereWhiteUnicornMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereWizardBoots($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereWolfCloak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereWolfMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereYewCompBow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereZamorakCloak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediumTreasureTrails whereZamorakMitre($value)
 * @mixin \Eloquent
 */
class MediumTreasureTrails extends Model
{
    protected $table = 'medium_treasure_trails';

    protected $fillable = [
        'obtained',
        'kill_count',
        'ranger_boots',
        'wizard_boots',
        'holy_sandals',
        'climbing_boots_(g)',
        'spiked_manacles',
        'adamant_full_helm_(t)',
        'adamant_platebody_(t)',
        'adamant_platelegs_(t)',
        'adamant_plateskirt_(t)',
        'adamant_kiteshield_(t)',
        'adamant_full_helm_(g)',
        'adamant_platebody_(g)',
        'adamant_platelegs_(g)',
        'adamant_plateskirt_(g)',
        'adamant_kiteshield_(g)',
        'adamant_shield_(h1)',
        'adamant_shield_(h2)',
        'adamant_shield_(h3)',
        'adamant_shield_(h4)',
        'adamant_shield_(h5)',
        'adamant_helm_(h1)',
        'adamant_helm_(h2)',
        'adamant_helm_(h3)',
        'adamant_helm_(h4)',
        'adamant_helm_(h5)',
        'adamant_platebody_(h1)',
        'adamant_platebody_(h2)',
        'adamant_platebody_(h3)',
        'adamant_platebody_(h4)',
        'adamant_platebody_(h5)',
        'mithril_full_helm_(g)',
        'mithril_platebody_(g)',
        'mithril_platelegs_(g)',
        'mithril_plateskirt_(g)',
        'mithril_kiteshield_(g)',
        'mithril_full_helm_(t)',
        'mithril_platebody_(t)',
        'mithril_platelegs_(t)',
        'mithril_plateskirt_(t)',
        'mithril_kiteshield_(t)',
        'green_dhide_body_(g)',
        'green_dhide_body_(t)',
        'green_dhide_chaps_(g)',
        'green_dhide_chaps_(t)',
        'saradomin_mitre',
        'saradomin_cloak',
        'guthix_mitre',
        'guthix_cloak',
        'zamorak_mitre',
        'zamorak_cloak',
        'ancient_mitre',
        'ancient_cloak',
        'ancient_stole',
        'ancient_crozier',
        'armadyl_mitre',
        'armadyl_cloak',
        'armadyl_stole',
        'armadyl_crozier',
        'bandos_mitre',
        'bandos_cloak',
        'bandos_stole',
        'bandos_crozier',
        'red_boater',
        'green_boater',
        'orange_boater',
        'black_boater',
        'blue_boater',
        'pink_boater',
        'purple_boater',
        'white_boater',
        'red_headband',
        'black_headband',
        'brown_headband',
        'white_headband',
        'blue_headband',
        'gold_headband',
        'pink_headband',
        'green_headband',
        'crier_hat',
        'crier_coat',
        'crier_bell',
        'adamant_cane',
        'arceuus_banner',
        'piscarilius_banner',
        'hosidius_banner',
        'shayzien_banner',
        'lovakengj_banner',
        'cabbage_round_shield',
        'black_unicorn_mask',
        'white_unicorn_mask',
        'cat_mask',
        'penguin_mask',
        'leprechaun_hat',
        'black_leprechaun_hat',
        'wolf_mask',
        'wolf_cloak',
        'purple_elegant_shirt',
        'purple_elegant_blouse',
        'purple_elegant_legs',
        'purple_elegant_skirt',
        'black_elegant_shirt',
        'white_elegant_blouse',
        'black_elegant_legs',
        'white_elegant_skirt',
        'pink_elegant_shirt',
        'pink_elegant_blouse',
        'pink_elegant_legs',
        'pink_elegant_skirt',
        'gold_elegant_shirt',
        'gold_elegant_blouse',
        'gold_elegant_legs',
        'gold_elegant_skirt',
        'gnomish_firelighter',
        'strength_amulet_(t)',
        'yew_comp_bow',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
