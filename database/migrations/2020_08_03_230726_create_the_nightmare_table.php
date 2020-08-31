<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTheNightmareTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('the_nightmare', function (Blueprint $table) {
            $table->id();
            $table->integer('account_id')->unsigned()->unique();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('little_nightmare')->default(0)->unsigned();
            $table->integer('inquisitors_mace')->default(0)->unsigned();
            $table->integer('inquisitors_great_helm')->default(0)->unsigned();
            $table->integer('inquisitors_hauberk')->default(0)->unsigned();
            $table->integer('inquisitors_plateskirt')->default(0)->unsigned();
            $table->integer('nightmare_staff')->default(0)->unsigned();
            $table->integer('volatile_orb')->default(0)->unsigned();
            $table->integer('harmonised_orb')->default(0)->unsigned();
            $table->integer('eldritch_orb')->default(0)->unsigned();
            $table->integer('jar_of_dreams')->default(0)->unsigned();
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
        Schema::dropIfExists('the_nightmare');
    }
}
