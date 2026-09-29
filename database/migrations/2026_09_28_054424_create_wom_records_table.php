<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Wise Old Man personal records: the best gain per period and metric,
     * synced by SyncWomMilestones. Five-minute records are not stored.
     */
    public function up(): void
    {
        Schema::create('wom_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('period', 10);
            $table->string('metric', 60);
            $table->double('value');
            $table->timestamp('achieved_at');
            $table->timestamps();

            $table->unique(['account_id', 'period', 'metric']);
            $table->index(['account_id', 'achieved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wom_records');
    }
};
