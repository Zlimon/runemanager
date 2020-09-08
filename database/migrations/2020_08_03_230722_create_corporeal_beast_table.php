<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporealBeastTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('corporeal_beast', function (Blueprint $table) {
            $table->id();
            $table->integer('account_id')->unsigned()->unique();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('rank')->default(0)->unsigned();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('pet_dark_core')->default(0)->unsigned();
            $table->integer('elysian_sigil')->default(0)->unsigned();
            $table->integer('spectral_sigil')->default(0)->unsigned();
            $table->integer('arcane_sigil')->default(0)->unsigned();
            $table->integer('holy_elixir')->default(0)->unsigned();
            $table->integer('spirit_shield')->default(0)->unsigned();
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
        Schema::dropIfExists('corporeal_beast');
    }
}
