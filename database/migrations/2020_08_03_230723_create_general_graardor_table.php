<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralGraardorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('general_graardor', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned()->unique();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('pet_general_graardor')->default(0)->unsigned();
            $table->integer('bandos_chestplate')->default(0)->unsigned();
            $table->integer('bandos_tassets')->default(0)->unsigned();
            $table->integer('bandos_boots')->default(0)->unsigned();
            $table->integer('bandos_hilt')->default(0)->unsigned();
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
        Schema::dropIfExists('general_graardor');
    }
}
