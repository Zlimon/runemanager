<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKrilTsutsarothTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kril_tsutsaroth', function (Blueprint $table) {
            $table->id();
            $table->integer('account_id')->unsigned()->unique();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('pet_kril_tsutsaroth')->default(0)->unsigned();
            $table->integer('staff_of_the_dead')->default(0)->unsigned();
            $table->integer('zamorakian_spear')->default(0)->unsigned();
            $table->integer('steam_battlestaff')->default(0)->unsigned();
            $table->integer('zamorak_hilt')->default(0)->unsigned();
            $table->integer('godsword_shard_1')->default(0)->unsigned();
            $table->integer('godsword_shard_2')->default(0)->unsigned();
            $table->integer('godsword_shard_3')->default(0)->unsigned();
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
        Schema::dropIfExists('kril_tsutsaroth');
    }
}
