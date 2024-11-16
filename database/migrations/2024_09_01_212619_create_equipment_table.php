<?php

use App\Models\Account;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Account::class)->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('head')->nullable();
            $table->unsignedSmallInteger('cape')->nullable();
            $table->unsignedSmallInteger('neck')->nullable();
            $table->unsignedSmallInteger('ammo')->nullable();
            $table->unsignedSmallInteger('weapon')->nullable();
            $table->unsignedSmallInteger('body')->nullable();
            $table->unsignedSmallInteger('shield')->nullable();
            $table->unsignedSmallInteger('legs')->nullable();
            $table->unsignedSmallInteger('hands')->nullable();
            $table->unsignedSmallInteger('feet')->nullable();
            $table->unsignedSmallInteger('ring')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
