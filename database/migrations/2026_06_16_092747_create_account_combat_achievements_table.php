<?php

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
        Schema::create('account_combat_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete()->unique();
            // Total Combat Achievement points (CA_POINTS), the leaderboard metric.
            $table->unsignedInteger('points')->default(0);
            // {tier: status} — per-tier locked/in_progress/completed (SPEC §7.1).
            $table->jsonb('tiers');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_combat_achievements');
    }
};
