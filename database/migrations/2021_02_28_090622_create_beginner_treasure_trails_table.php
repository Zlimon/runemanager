<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBeginnerTreasureTrailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beginner_treasure_trails', function (Blueprint $table) {
            $table->id();
            $table->integer('account_id')->unsigned()->unique();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('rank')->default(0)->unsigned();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('mole_slippers')->default(0)->unsigned();
            $table->integer('frog_slippers')->default(0)->unsigned();
            $table->integer('bear_feet')->default(0)->unsigned();
            $table->integer('demon_feet')->default(0)->unsigned();
            $table->integer('jester_cape')->default(0)->unsigned();
            $table->integer('shoulder_parrot')->default(0)->unsigned();
            $table->integer('monks_robe_top_(t)')->default(0)->unsigned();
            $table->integer('monks_robe_(t)')->default(0)->unsigned();
            $table->integer('amulet_of_defence_(t)')->default(0)->unsigned();
            $table->integer('sandwich_lady_hat')->default(0)->unsigned();
            $table->integer('sandwich_lady_top')->default(0)->unsigned();
            $table->integer('sandwich_lady_bottom')->default(0)->unsigned();
            $table->integer('rune_scimitar_ornament_kit_(guthix)')->default(0)->unsigned();
            $table->integer('rune_scimitar_ornament_kit_(saradomin)')->default(0)->unsigned();
            $table->integer('rune_scimitar_ornament_kit_(zamorak)')->default(0)->unsigned();
            $table->integer('black_pickaxe')->default(0)->unsigned();
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
        Schema::dropIfExists('beginner_treasure_trails');
    }
}
