<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWintertodtTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wintertodt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('rank')->default(0)->unsigned();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('phoenix')->default(0)->unsigned();
            $table->integer('tome_of_fire_(empty)')->default(0)->unsigned();
            $table->integer('burnt_page')->default(0)->unsigned();
            $table->integer('pyromancer_garb')->default(0)->unsigned();
            $table->integer('pyromancer_hood')->default(0)->unsigned();
            $table->integer('pyromancer_robe')->default(0)->unsigned();
            $table->integer('pyromancer_boots')->default(0)->unsigned();
            $table->integer('warm_gloves')->default(0)->unsigned();
            $table->integer('bruma_torch')->default(0)->unsigned();
            $table->integer('dragon_axe')->default(0)->unsigned();
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
        Schema::dropIfExists('wintertodt');
    }
}
