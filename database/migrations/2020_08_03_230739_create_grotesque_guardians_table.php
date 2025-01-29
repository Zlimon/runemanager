<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrotesqueGuardiansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grotesque_guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('rank')->default(0)->unsigned();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('noon')->default(0)->unsigned();
            $table->integer('black_tourmaline_core')->default(0)->unsigned();
            $table->integer('granite_gloves')->default(0)->unsigned();
            $table->integer('granite_ring')->default(0)->unsigned();
            $table->integer('granite_hammer')->default(0)->unsigned();
            $table->integer('jar_of_stone')->default(0)->unsigned();
            $table->integer('granite_dust')->default(0)->unsigned();
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
        Schema::dropIfExists('grotesque_guardians');
    }
}
