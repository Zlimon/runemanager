<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlackKnightTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('black_knight', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('account_id')->unsigned()->unique();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('uncut_sapphire')->default(0)->unsigned();
            $table->integer('uncut_emerald')->default(0)->unsigned();
            $table->integer('uncut_ruby')->default(0)->unsigned();
            $table->integer('chaos_talisman')->default(0)->unsigned();
            $table->integer('nature_talisman')->default(0)->unsigned();
            $table->integer('uncut_diamond')->default(0)->unsigned();
            $table->integer('rune_javelin')->default(0)->unsigned();
            $table->integer('loop_half_of_key')->default(0)->unsigned();
            $table->integer('tooth_half_of_key')->default(0)->unsigned();
            $table->integer('rune_spear')->default(0)->unsigned();
            $table->integer('shield_left_half')->default(0)->unsigned();
            $table->integer('dragon_spear')->default(0)->unsigned();
            $table->integer('bones')->default(0)->unsigned();
            $table->integer('law_rune')->default(0)->unsigned();
            $table->integer('cosmic_rune')->default(0)->unsigned();
            $table->integer('mind_rune')->default(0)->unsigned();
            $table->integer('grimy_guam_leaf')->default(0)->unsigned();
            $table->integer('grimy_marrentill')->default(0)->unsigned();
            $table->integer('grimy_tarromin')->default(0)->unsigned();
            $table->integer('grimy_harralander')->default(0)->unsigned();
            $table->integer('grimy_ranarr_weed')->default(0)->unsigned();
            $table->integer('grimy_irit_leaf')->default(0)->unsigned();
            $table->integer('grimy_avantoe')->default(0)->unsigned();
            $table->integer('iron_sword')->default(0)->unsigned();
            $table->integer('grimy_kwuarm')->default(0)->unsigned();
            $table->integer('grimy_cadantine')->default(0)->unsigned();
            $table->integer('grimy_lantadyme')->default(0)->unsigned();
            $table->integer('grimy_dwarf_weed')->default(0)->unsigned();
            $table->integer('potato_seed')->default(0)->unsigned();
            $table->integer('onion_seed')->default(0)->unsigned();
            $table->integer('cabbage_seed')->default(0)->unsigned();
            $table->integer('tomato_seed')->default(0)->unsigned();
            $table->integer('sweetcorn_seed')->default(0)->unsigned();
            $table->integer('strawberry_seed')->default(0)->unsigned();
            $table->integer('iron_full_helm')->default(0)->unsigned();
            $table->integer('watermelon_seed')->default(0)->unsigned();
            $table->integer('snape_grass_seed')->default(0)->unsigned();
            $table->integer('steel_bar')->default(0)->unsigned();
            $table->integer('tin_ore')->default(0)->unsigned();
            $table->integer('pot_of_flour')->default(0)->unsigned();
            $table->integer('coins')->default(0)->unsigned();
            $table->integer('steel_mace')->default(0)->unsigned();
            $table->integer('bread')->default(0)->unsigned();
            $table->integer('mithril_arrow')->default(0)->unsigned();
            $table->integer('looting_bag')->default(0)->unsigned();
            $table->integer('body_rune')->default(0)->unsigned();
            $table->integer('chaos_rune')->default(0)->unsigned();
            $table->integer('earth_rune')->default(0)->unsigned();
            $table->integer('death_rune')->default(0)->unsigned();
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
        Schema::dropIfExists('black_knight');
    }
}
