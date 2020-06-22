<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScorpiasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scorpias', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned()->unique();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('malediction_shard_3')->default(0)->unsigned();
            $table->integer('scorpias_offspring')->default(0)->unsigned();
            $table->integer('odium_shard_3')->default(0)->unsigned();
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
        Schema::dropIfExists('scorpias');
    }
}
