<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTheInfernosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('the_infernos', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned()->unique();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('infernal_cape')->default(0)->unsigned();
            $table->integer('jal-nib-rek')->default(0)->unsigned();
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
        Schema::dropIfExists('the_infernos');
    }
}
