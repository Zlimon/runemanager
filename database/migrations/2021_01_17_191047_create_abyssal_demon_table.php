<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAbyssalDemonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('abyssal_demon', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('account_id')->unsigned()->unique();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('abyssal_whip')->default(0)->unsigned();
            $table->integer('abyssal_dagger')->default(0)->unsigned();
            $table->integer('abyssal_head')->default(0)->unsigned();
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
        Schema::dropIfExists('abyssal_demon');
    }
}
