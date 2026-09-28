<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Wise Old Man achievements (99s, xp and kill-count thresholds), synced by
     * SyncWomMilestones for the Adventurer's Log and the Gains tab.
     */
    public function up(): void
    {
        Schema::create('wom_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('metric', 60);
            $table->string('measure', 20);
            $table->unsignedBigInteger('threshold');
            $table->timestamp('achieved_at')->nullable();
            $table->unsignedBigInteger('accuracy')->nullable();
            $table->boolean('legacy')->default(false);
            $table->timestamps();

            $table->unique(['account_id', 'name']);
            $table->index(['account_id', 'achieved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wom_achievements');
    }
};
