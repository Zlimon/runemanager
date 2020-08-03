<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTheGauntletTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('the_gauntlet', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned()->unique();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('youngllef')->default(0)->unsigned();
            $table->integer('crystal_armour_seed')->default(0)->unsigned();
            $table->integer('crystal_weapon_seed')->default(0)->unsigned();
            $table->integer('blade_of_saeldor_(inactive)')->default(0)->unsigned();
            $table->integer('gauntlet_cape')->default(0)->unsigned();
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
        Schema::dropIfExists('the_gauntlet');
    }
}
