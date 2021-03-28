<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterTreasureTrailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_treasure_trails', function (Blueprint $table) {
            $table->id();
            $table->integer('account_id')->unsigned()->unique();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('rank')->default(0)->unsigned();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('bloodhound')->default(0)->unsigned();
            $table->integer('3rd_age_pickaxe')->default(0)->unsigned();
            $table->integer('3rd_age_axe')->default(0)->unsigned();
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
            $table->integer('3rd_age_druidic_robe_bottoms')->default(0)->unsigned();
            $table->integer('3rd_age_druidic_robe_top')->default(0)->unsigned();
            $table->integer('3rd_age_druidic_staff')->default(0)->unsigned();
            $table->integer('3rd_age_druidic_cloak')->default(0)->unsigned();
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
            $table->integer('bucket_helm_(g)')->default(0)->unsigned();
            $table->integer('ring_of_coins')->default(0)->unsigned();
            $table->integer('armadyl_godsword_ornament_kit')->default(0)->unsigned();
            $table->integer('bandos_godsword_ornament_kit')->default(0)->unsigned();
            $table->integer('saradomin_godsword_ornament_kit')->default(0)->unsigned();
            $table->integer('zamorak_godsword_ornament_kit')->default(0)->unsigned();
            $table->integer('occult_ornament_kit')->default(0)->unsigned();
            $table->integer('torture_ornament_kit')->default(0)->unsigned();
            $table->integer('anguish_ornament_kit')->default(0)->unsigned();
            $table->integer('dragon_defender_ornament_kit')->default(0)->unsigned();
            $table->integer('dragon_kiteshield_ornament_kit')->default(0)->unsigned();
            $table->integer('dragon_platebody_ornament_kit')->default(0)->unsigned();
            $table->integer('tormented_ornament_kit')->default(0)->unsigned();
            $table->integer('hood_of_darkness')->default(0)->unsigned();
            $table->integer('robe_top_of_darkness')->default(0)->unsigned();
            $table->integer('robe_bottom_of_darkness')->default(0)->unsigned();
            $table->integer('gloves_of_darkness')->default(0)->unsigned();
            $table->integer('boots_of_darkness')->default(0)->unsigned();
            $table->integer('samurai_kasa')->default(0)->unsigned();
            $table->integer('samurai_shirt')->default(0)->unsigned();
            $table->integer('samurai_greaves')->default(0)->unsigned();
            $table->integer('samurai_boots')->default(0)->unsigned();
            $table->integer('samurai_gloves')->default(0)->unsigned();
            $table->integer('ankou_mask')->default(0)->unsigned();
            $table->integer('ankou_top')->default(0)->unsigned();
            $table->integer('ankou_gloves')->default(0)->unsigned();
            $table->integer('ankou_socks')->default(0)->unsigned();
            $table->integer('ankous_leggings')->default(0)->unsigned();
            $table->integer('mummys_head')->default(0)->unsigned();
            $table->integer('mummys_feet')->default(0)->unsigned();
            $table->integer('mummys_hands')->default(0)->unsigned();
            $table->integer('mummys_legs')->default(0)->unsigned();
            $table->integer('mummys_body')->default(0)->unsigned();
            $table->integer('shayzien_hood')->default(0)->unsigned();
            $table->integer('hosidius_hood')->default(0)->unsigned();
            $table->integer('arceuus_hood')->default(0)->unsigned();
            $table->integer('piscarilius_hood')->default(0)->unsigned();
            $table->integer('lovakengj_hood')->default(0)->unsigned();
            $table->integer('lesser_demon_mask')->default(0)->unsigned();
            $table->integer('greater_demon_mask')->default(0)->unsigned();
            $table->integer('black_demon_mask')->default(0)->unsigned();
            $table->integer('jungle_demon_mask')->default(0)->unsigned();
            $table->integer('old_demon_mask')->default(0)->unsigned();
            $table->integer('left_eye_patch')->default(0)->unsigned();
            $table->integer('bowl_wig')->default(0)->unsigned();
            $table->integer('ale_of_the_gods')->default(0)->unsigned();
            $table->integer('obsidian_cape_(r)')->default(0)->unsigned();
            $table->integer('half_moon_spectacles')->default(0)->unsigned();
            $table->integer('fancy_tiara')->default(0)->unsigned();
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
        Schema::dropIfExists('master_treasure_trails');
    }
}
