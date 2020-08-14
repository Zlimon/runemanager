<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDagannothKingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dagannoth_kings', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned()->unique();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('pet_dagannoth_prime')->default(0)->unsigned();
            $table->integer('pet_dagannoth_supreme')->default(0)->unsigned();
            $table->integer('pet_dagannoth_rex')->default(0)->unsigned();
            $table->integer('berserker_ring')->default(0)->unsigned();
            $table->integer('archers_ring')->default(0)->unsigned();
            $table->integer('seers_ring')->default(0)->unsigned();
            $table->integer('warrior_ring')->default(0)->unsigned();
            $table->integer('dragon_axe')->default(0)->unsigned();
            $table->integer('seercull')->default(0)->unsigned();
            $table->integer('mud_battlestaff')->default(0)->unsigned();
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
        Schema::dropIfExists('dagannoth_kings');
    }
}
