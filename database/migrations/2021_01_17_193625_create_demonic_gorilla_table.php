<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDemonicGorillaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('demonic_gorilla', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('account_id')->unsigned()->unique();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('zenyte_shard')->default(0)->unsigned();
            $table->integer('ballista_limbs')->default(0)->unsigned();
            $table->integer('ballista_springs')->default(0)->unsigned();
            $table->integer('light_frame')->default(0)->unsigned();
            $table->integer('heavy_frame')->default(0)->unsigned();
            $table->integer('monkey_tail')->default(0)->unsigned();
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
        Schema::dropIfExists('demonic_gorilla');
    }
}
