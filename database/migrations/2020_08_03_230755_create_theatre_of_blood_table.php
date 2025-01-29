<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTheatreOfBloodTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('theatre_of_blood', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('rank')->default(0)->unsigned();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('lil_zik')->default(0)->unsigned();
            $table->integer('scythe_of_vitur_(uncharged)')->default(0)->unsigned();
            $table->integer('ghrazi_rapier')->default(0)->unsigned();
            $table->integer('sanguinesti_staff_(uncharged)')->default(0)->unsigned();
            $table->integer('justiciar_faceguard')->default(0)->unsigned();
            $table->integer('justiciar_chestguard')->default(0)->unsigned();
            $table->integer('justiciar_legguards')->default(0)->unsigned();
            $table->integer('avernic_defender_hilt')->default(0)->unsigned();
            $table->integer('vial_of_blood')->default(0)->unsigned();
            $table->integer('sinhaza_shroud_tier_1')->default(0)->unsigned();
            $table->integer('sinhaza_shroud_tier_2')->default(0)->unsigned();
            $table->integer('sinhaza_shroud_tier_3')->default(0)->unsigned();
            $table->integer('sinhaza_shroud_tier_4')->default(0)->unsigned();
            $table->integer('sinhaza_shroud_tier_5')->default(0)->unsigned();
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
        Schema::dropIfExists('theatre_of_blood');
    }
}
