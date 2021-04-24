<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEasyTreasureTrailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('easy_treasure_trails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('rank')->default(0)->unsigned();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('team_cape_zero')->default(0)->unsigned();
            $table->integer('team_cape_i')->default(0)->unsigned();
            $table->integer('team_cape_x')->default(0)->unsigned();
            $table->integer('cape_of_skulls')->default(0)->unsigned();
            $table->integer('golden_chefs_hat')->default(0)->unsigned();
            $table->integer('golden_apron')->default(0)->unsigned();
            $table->integer('wooden_shield_(g)')->default(0)->unsigned();
            $table->integer('black_full_helm_(t)')->default(0)->unsigned();
            $table->integer('black_platebody_(t)')->default(0)->unsigned();
            $table->integer('black_platelegs_(t)')->default(0)->unsigned();
            $table->integer('black_plateskirt_(t)')->default(0)->unsigned();
            $table->integer('black_kiteshield_(t)')->default(0)->unsigned();
            $table->integer('black_full_helm_(g)')->default(0)->unsigned();
            $table->integer('black_platebody_(g)')->default(0)->unsigned();
            $table->integer('black_platelegs_(g)')->default(0)->unsigned();
            $table->integer('black_plateskirt_(g)')->default(0)->unsigned();
            $table->integer('black_kiteshield_(g)')->default(0)->unsigned();
            $table->integer('black_shield_(h1)')->default(0)->unsigned();
            $table->integer('black_shield_(h2)')->default(0)->unsigned();
            $table->integer('black_shield_(h3)')->default(0)->unsigned();
            $table->integer('black_shield_(h4)')->default(0)->unsigned();
            $table->integer('black_shield_(h5)')->default(0)->unsigned();
            $table->integer('black_helm_(h1)')->default(0)->unsigned();
            $table->integer('black_helm_(h2)')->default(0)->unsigned();
            $table->integer('black_helm_(h3)')->default(0)->unsigned();
            $table->integer('black_helm_(h4)')->default(0)->unsigned();
            $table->integer('black_helm_(h5)')->default(0)->unsigned();
            $table->integer('black_platebody_(h1)')->default(0)->unsigned();
            $table->integer('black_platebody_(h2)')->default(0)->unsigned();
            $table->integer('black_platebody_(h3)')->default(0)->unsigned();
            $table->integer('black_platebody_(h4)')->default(0)->unsigned();
            $table->integer('black_platebody_(h5)')->default(0)->unsigned();
            $table->integer('steel_full_helm_(t)')->default(0)->unsigned();
            $table->integer('steel_platebody_(t)')->default(0)->unsigned();
            $table->integer('steel_platelegs_(t)')->default(0)->unsigned();
            $table->integer('steel_plateskirt_(t)')->default(0)->unsigned();
            $table->integer('steel_kiteshield_(t)')->default(0)->unsigned();
            $table->integer('steel_full_helm_(g)')->default(0)->unsigned();
            $table->integer('steel_platebody_(g)')->default(0)->unsigned();
            $table->integer('steel_platelegs_(g)')->default(0)->unsigned();
            $table->integer('steel_plateskirt_(g)')->default(0)->unsigned();
            $table->integer('steel_kiteshield_(g)')->default(0)->unsigned();
            $table->integer('iron_platebody_(t)')->default(0)->unsigned();
            $table->integer('iron_platelegs_(t)')->default(0)->unsigned();
            $table->integer('iron_plateskirt_(t)')->default(0)->unsigned();
            $table->integer('iron_kiteshield_(t)')->default(0)->unsigned();
            $table->integer('iron_full_helm_(t)')->default(0)->unsigned();
            $table->integer('iron_platebody_(g)')->default(0)->unsigned();
            $table->integer('iron_platelegs_(g)')->default(0)->unsigned();
            $table->integer('iron_plateskirt_(g)')->default(0)->unsigned();
            $table->integer('iron_kiteshield_(g)')->default(0)->unsigned();
            $table->integer('iron_full_helm_(g)')->default(0)->unsigned();
            $table->integer('bronze_platebody_(t)')->default(0)->unsigned();
            $table->integer('bronze_platelegs_(t)')->default(0)->unsigned();
            $table->integer('bronze_plateskirt_(t)')->default(0)->unsigned();
            $table->integer('bronze_kiteshield_(t)')->default(0)->unsigned();
            $table->integer('bronze_full_helm_(t)')->default(0)->unsigned();
            $table->integer('bronze_platebody_(g)')->default(0)->unsigned();
            $table->integer('bronze_platelegs_(g)')->default(0)->unsigned();
            $table->integer('bronze_plateskirt_(g)')->default(0)->unsigned();
            $table->integer('bronze_kiteshield_(g)')->default(0)->unsigned();
            $table->integer('bronze_full_helm_(g)')->default(0)->unsigned();
            $table->integer('studded_body_(g)')->default(0)->unsigned();
            $table->integer('studded_chaps_(g)')->default(0)->unsigned();
            $table->integer('studded_body_(t)')->default(0)->unsigned();
            $table->integer('studded_chaps_(t)')->default(0)->unsigned();
            $table->integer('leather_body_(g)')->default(0)->unsigned();
            $table->integer('leather_chaps_(g)')->default(0)->unsigned();
            $table->integer('blue_wizard_hat_(g)')->default(0)->unsigned();
            $table->integer('blue_wizard_robe_(g)')->default(0)->unsigned();
            $table->integer('blue_skirt_(g)')->default(0)->unsigned();
            $table->integer('blue_wizard_hat_(t)')->default(0)->unsigned();
            $table->integer('blue_wizard_robe_(t)')->default(0)->unsigned();
            $table->integer('blue_skirt_(t)')->default(0)->unsigned();
            $table->integer('black_wizard_hat_(g)')->default(0)->unsigned();
            $table->integer('black_wizard_robe_(g)')->default(0)->unsigned();
            $table->integer('black_skirt_(g)')->default(0)->unsigned();
            $table->integer('black_wizard_hat_(t)')->default(0)->unsigned();
            $table->integer('black_wizard_robe_(t)')->default(0)->unsigned();
            $table->integer('black_skirt_(t)')->default(0)->unsigned();
            $table->integer('monks_robe_top_(g)')->default(0)->unsigned();
            $table->integer('monks_robe_(g)')->default(0)->unsigned();
            $table->integer('saradomin_robe_top')->default(0)->unsigned();
            $table->integer('saradomin_robe_legs')->default(0)->unsigned();
            $table->integer('guthix_robe_top')->default(0)->unsigned();
            $table->integer('guthix_robe_legs')->default(0)->unsigned();
            $table->integer('zamorak_robe_top')->default(0)->unsigned();
            $table->integer('zamorak_robe_legs')->default(0)->unsigned();
            $table->integer('ancient_robe_top')->default(0)->unsigned();
            $table->integer('ancient_robe_legs')->default(0)->unsigned();
            $table->integer('armadyl_robe_top')->default(0)->unsigned();
            $table->integer('armadyl_robe_legs')->default(0)->unsigned();
            $table->integer('bandos_robe_top')->default(0)->unsigned();
            $table->integer('bandos_robe_legs')->default(0)->unsigned();
            $table->integer('bobs_red_shirt')->default(0)->unsigned();
            $table->integer('bobs_green_shirt')->default(0)->unsigned();
            $table->integer('bobs_blue_shirt')->default(0)->unsigned();
            $table->integer('bobs_black_shirt')->default(0)->unsigned();
            $table->integer('bobs_purple_shirt')->default(0)->unsigned();
            $table->integer('highwayman_mask')->default(0)->unsigned();
            $table->integer('blue_beret')->default(0)->unsigned();
            $table->integer('black_beret')->default(0)->unsigned();
            $table->integer('white_beret')->default(0)->unsigned();
            $table->integer('red_beret')->default(0)->unsigned();
            $table->integer('a_powdered_wig')->default(0)->unsigned();
            $table->integer('beanie')->default(0)->unsigned();
            $table->integer('imp_mask')->default(0)->unsigned();
            $table->integer('goblin_mask')->default(0)->unsigned();
            $table->integer('sleeping_cap')->default(0)->unsigned();
            $table->integer('flared_trousers')->default(0)->unsigned();
            $table->integer('pantaloons')->default(0)->unsigned();
            $table->integer('black_cane')->default(0)->unsigned();
            $table->integer('staff_of_bob_the_cat')->default(0)->unsigned();
            $table->integer('red_elegant_shirt')->default(0)->unsigned();
            $table->integer('red_elegant_blouse')->default(0)->unsigned();
            $table->integer('red_elegant_legs')->default(0)->unsigned();
            $table->integer('red_elegant_skirt')->default(0)->unsigned();
            $table->integer('green_elegant_shirt')->default(0)->unsigned();
            $table->integer('green_elegant_blouse')->default(0)->unsigned();
            $table->integer('green_elegant_legs')->default(0)->unsigned();
            $table->integer('green_elegant_skirt')->default(0)->unsigned();
            $table->integer('blue_elegant_shirt')->default(0)->unsigned();
            $table->integer('blue_elegant_blouse')->default(0)->unsigned();
            $table->integer('blue_elegant_legs')->default(0)->unsigned();
            $table->integer('blue_elegant_skirt')->default(0)->unsigned();
            $table->integer('amulet_of_magic_(t)')->default(0)->unsigned();
            $table->integer('amulet_of_power_(t)')->default(0)->unsigned();
            $table->integer('black_pickaxe')->default(0)->unsigned();
            $table->integer('ham_joint')->default(0)->unsigned();
            $table->integer('rain_bow')->default(0)->unsigned();
            $table->integer('willow_comp_bow')->default(0)->unsigned();
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
        Schema::dropIfExists('easy_treasure_trails');
    }
}