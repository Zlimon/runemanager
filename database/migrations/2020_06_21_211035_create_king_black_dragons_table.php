<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKingBlackDragonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('king_black_dragons', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned()->unique();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('kbd_heads')->default(0)->unsigned();
            $table->integer('prince_black_dragon')->default(0)->unsigned();
            $table->integer('dragon_pickaxe')->default(0)->unsigned();
            $table->integer('draconic_visage')->default(0)->unsigned();
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
        Schema::dropIfExists('king_black_dragons');
    }
}
