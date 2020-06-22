<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCallistosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('callistos', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned()->unique();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('dragon_2h_sword')->default(0)->unsigned();
            $table->integer('dragon_pickaxe')->default(0)->unsigned();
            $table->integer('callisto_cub')->default(0)->unsigned();
            $table->integer('tyrannical_ring')->default(0)->unsigned();
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
        Schema::dropIfExists('callistos');
    }
}
