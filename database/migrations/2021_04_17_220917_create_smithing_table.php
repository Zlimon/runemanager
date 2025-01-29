<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmithingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('smithing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained();
            $table->integer('rank')->default(0);
            $table->integer('level')->default(1);
            $table->bigInteger('xp')->default(0);
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
        Schema::dropIfExists('smithing');
    }
}
