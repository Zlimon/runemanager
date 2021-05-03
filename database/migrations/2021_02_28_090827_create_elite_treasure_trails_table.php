<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEliteTreasureTrailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('elite_treasure_trails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('rank')->default(0)->unsigned();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('3rd_age_longsword')->default(0)->unsigned();
            $table->integer('3rd_age_wand')->default(0)->unsigned();
            $table->integer('3rd_age_cloak')->default(0)->unsigned();
            $table->integer('3rd_age_bow')->default(0)->unsigned();
            $table->integer('3rd_age_range_coif')->default(0)->unsigned();
            $table->integer('3rd_age_range_top')->default(0)->unsigned();
            $table->integer('3rd_age_range_legs')->default(0)->unsigned();
            $table->integer('3rd_age_vambraces')->default(0)->unsigned();
            $table->integer('3rd_age_robe_top')->default(0)->unsigned();
            $table->integer('3rd_age_robe')->default(0)->unsigned();
            $table->integer('3rd_age_mage_hat')->default(0)->unsigned();
            $table->integer('3rd_age_amulet')->default(0)->unsigned();
            $table->integer('3rd_age_plateskirt')->default(0)->unsigned();
            $table->integer('3rd_age_platelegs')->default(0)->unsigned();
            $table->integer('3rd_age_platebody')->default(0)->unsigned();
            $table->integer('3rd_age_full_helmet')->default(0)->unsigned();
            $table->integer('3rd_age_kiteshield')->default(0)->unsigned();
            $table->integer('ring_of_3rd_age')->default(0)->unsigned();
            $table->integer('gilded_scimitar')->default(0)->unsigned();
            $table->integer('gilded_boots')->default(0)->unsigned();
            $table->integer('gilded_platebody')->default(0)->unsigned();
            $table->integer('gilded_platelegs')->default(0)->unsigned();
            $table->integer('gilded_plateskirt')->default(0)->unsigned();
            $table->integer('gilded_full_helm')->default(0)->unsigned();
            $table->integer('gilded_kiteshield')->default(0)->unsigned();
            $table->integer('gilded_med_helm')->default(0)->unsigned();
            $table->integer('gilded_chainbody')->default(0)->unsigned();
            $table->integer('gilded_sq_shield')->default(0)->unsigned();
            $table->integer('gilded_2h_sword')->default(0)->unsigned();
            $table->integer('gilded_spear')->default(0)->unsigned();
            $table->integer('gilded_hasta')->default(0)->unsigned();
            $table->integer('gilded_coif')->default(0)->unsigned();
            $table->integer('gilded_dhide_vambraces')->default(0)->unsigned();
            $table->integer('gilded_dhide_body')->default(0)->unsigned();
            $table->integer('gilded_dhide_chaps')->default(0)->unsigned();
            $table->integer('gilded_pickaxe')->default(0)->unsigned();
            $table->integer('gilded_axe')->default(0)->unsigned();
            $table->integer('gilded_spade')->default(0)->unsigned();
            $table->integer('fury_ornament_kit')->default(0)->unsigned();
            $table->integer('dragon_chainbody_ornament_kit')->default(0)->unsigned();
            $table->integer('dragon_legs/skirt_ornament_kit')->default(0)->unsigned();
            $table->integer('dragon_sq_shield_ornament_kit')->default(0)->unsigned();
            $table->integer('dragon_full_helm_ornament_kit')->default(0)->unsigned();
            $table->integer('dragon_scimitar_ornament_kit')->default(0)->unsigned();
            $table->integer('light_infinity_colour_kit')->default(0)->unsigned();
            $table->integer('dark_infinity_colour_kit')->default(0)->unsigned();
            $table->integer('holy_wraps')->default(0)->unsigned();
            $table->integer('ranger_gloves')->default(0)->unsigned();
            $table->integer('rangers_tunic')->default(0)->unsigned();
            $table->integer('rangers_tights')->default(0)->unsigned();
            $table->integer('black_dhide_body_(g)')->default(0)->unsigned();
            $table->integer('black_dhide_chaps_(g)')->default(0)->unsigned();
            $table->integer('black_dhide_body_(t)')->default(0)->unsigned();
            $table->integer('black_dhide_chaps_(t)')->default(0)->unsigned();
            $table->integer('royal_crown')->default(0)->unsigned();
            $table->integer('royal_sceptre')->default(0)->unsigned();
            $table->integer('royal_gown_top')->default(0)->unsigned();
            $table->integer('royal_gown_bottom')->default(0)->unsigned();
            $table->integer('musketeer_hat')->default(0)->unsigned();
            $table->integer('musketeer_tabard')->default(0)->unsigned();
            $table->integer('musketeer_pants')->default(0)->unsigned();
            $table->integer('dark_tuxedo_jacket')->default(0)->unsigned();
            $table->integer('dark_trousers')->default(0)->unsigned();
            $table->integer('dark_tuxedo_shoes')->default(0)->unsigned();
            $table->integer('dark_tuxedo_cuffs')->default(0)->unsigned();
            $table->integer('dark_bow_tie')->default(0)->unsigned();
            $table->integer('light_tuxedo_jacket')->default(0)->unsigned();
            $table->integer('light_trousers')->default(0)->unsigned();
            $table->integer('light_tuxedo_shoes')->default(0)->unsigned();
            $table->integer('light_tuxedo_cuffs')->default(0)->unsigned();
            $table->integer('light_bow_tie')->default(0)->unsigned();
            $table->integer('arceuus_scarf')->default(0)->unsigned();
            $table->integer('hosidius_scarf')->default(0)->unsigned();
            $table->integer('piscarilius_scarf')->default(0)->unsigned();
            $table->integer('shayzien_scarf')->default(0)->unsigned();
            $table->integer('lovakengj_scarf')->default(0)->unsigned();
            $table->integer('bronze_dragon_mask')->default(0)->unsigned();
            $table->integer('iron_dragon_mask')->default(0)->unsigned();
            $table->integer('steel_dragon_mask')->default(0)->unsigned();
            $table->integer('mithril_dragon_mask')->default(0)->unsigned();
            $table->integer('adamant_dragon_mask')->default(0)->unsigned();
            $table->integer('rune_dragon_mask')->default(0)->unsigned();
            $table->integer('lava_dragon_mask')->default(0)->unsigned();
            $table->integer('ring_of_nature')->default(0)->unsigned();
            $table->integer('katana')->default(0)->unsigned();
            $table->integer('dragon_cane')->default(0)->unsigned();
            $table->integer('briefcase')->default(0)->unsigned();
            $table->integer('bucket_helm')->default(0)->unsigned();
            $table->integer('blacksmiths_helm')->default(0)->unsigned();
            $table->integer('deerstalker')->default(0)->unsigned();
            $table->integer('afro')->default(0)->unsigned();
            $table->integer('big_pirate_hat')->default(0)->unsigned();
            $table->integer('top_hat')->default(0)->unsigned();
            $table->integer('monocle')->default(0)->unsigned();
            $table->integer('sagacious_spectacles')->default(0)->unsigned();
            $table->integer('fremennik_kilt')->default(0)->unsigned();
            $table->integer('giant_boot')->default(0)->unsigned();
            $table->integer('uris_hat')->default(0)->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('elite_treasure_trails');
    }
}
