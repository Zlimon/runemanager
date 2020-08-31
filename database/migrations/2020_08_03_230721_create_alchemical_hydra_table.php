<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlchemicalHydraTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alchemical_hydra', function (Blueprint $table) {
            $table->id();
            $table->integer('account_id')->unsigned()->unique();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('ikkle_hydra')->default(0)->unsigned();
            $table->integer('hydras_claw')->default(0)->unsigned();
            $table->integer('hydra_tail')->default(0)->unsigned();
            $table->integer('hydra_leather')->default(0)->unsigned();
            $table->integer('hydras_fang')->default(0)->unsigned();
            $table->integer('hydras_eye')->default(0)->unsigned();
            $table->integer('hydras_heart')->default(0)->unsigned();
            $table->integer('dragon_knife')->default(0)->unsigned();
            $table->integer('dragon_thrownaxe')->default(0)->unsigned();
            $table->integer('jar_of_chemicals')->default(0)->unsigned();
            $table->integer('alchemical_hydra_heads')->default(0)->unsigned();
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
        Schema::dropIfExists('alchemical_hydra');
    }
}
