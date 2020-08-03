<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommanderZilyanaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commander_zilyana', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned()->unique();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('pet_zilyana')->default(0)->unsigned();
            $table->integer('armadyl_crossbow')->default(0)->unsigned();
            $table->integer('saradomin_hilt')->default(0)->unsigned();
            $table->integer('saradomin_sword')->default(0)->unsigned();
            $table->integer('saradomins_light')->default(0)->unsigned();
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
        Schema::dropIfExists('commander_zilyana');
    }
}
