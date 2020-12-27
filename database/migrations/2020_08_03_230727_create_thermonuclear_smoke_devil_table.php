<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThermonuclearSmokeDevilTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('thermonuclear_smoke_devil', function (Blueprint $table) {
            $table->id();
            $table->integer('account_id')->unsigned()->unique();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('rank')->default(0)->unsigned();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('pet_smoke_devil')->default(0)->unsigned();
            $table->integer('occult_necklace')->default(0)->unsigned();
            $table->integer('smoke_battlestaff')->default(0)->unsigned();
            $table->integer('dragon_chainbody')->default(0)->unsigned();
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
        Schema::dropIfExists('thermonuclear_smoke_devil');
    }
}
