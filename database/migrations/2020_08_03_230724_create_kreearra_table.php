<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKreearraTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kreearra', function (Blueprint $table) {
            $table->id();
            $table->integer('account_id')->unsigned()->unique();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('pet_kreearra')->default(0)->unsigned();
            $table->integer('armadyl_helmet')->default(0)->unsigned();
            $table->integer('armadyl_chestplate')->default(0)->unsigned();
            $table->integer('armadyl_chainskirt')->default(0)->unsigned();
            $table->integer('armadyl_hilt')->default(0)->unsigned();
            $table->integer('godsword_shard_1')->default(0)->unsigned();
            $table->integer('godsword_shard_2')->default(0)->unsigned();
            $table->integer('godsword_shard_3')->default(0)->unsigned();
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
        Schema::dropIfExists('kreearra');
    }
}
