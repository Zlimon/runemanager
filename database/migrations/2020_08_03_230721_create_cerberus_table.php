<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCerberusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cerberus', function (Blueprint $table) {
            $table->id();
            $table->integer('account_id')->unsigned()->unique();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('hellpuppy')->default(0)->unsigned();
            $table->integer('eternal_crystal')->default(0)->unsigned();
            $table->integer('pegasian_crystal')->default(0)->unsigned();
            $table->integer('primordial_crystal')->default(0)->unsigned();
            $table->integer('jar_of_souls')->default(0)->unsigned();
            $table->integer('smouldering_stone')->default(0)->unsigned();
            $table->integer('key_master_teleport')->default(0)->unsigned();
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
        Schema::dropIfExists('cerberus');
    }
}
