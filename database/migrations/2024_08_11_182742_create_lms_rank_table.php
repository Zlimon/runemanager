<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLmsRankTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('lms_rank', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->integer('rank')->default(0);
            $table->integer('kill_count')->default(0);
            $table->integer('obtained')->default(0);
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_rank');
    }
};