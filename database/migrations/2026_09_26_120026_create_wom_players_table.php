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
        Schema::create('wom_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('wom_player_id')->nullable()->index();
            $table->string('type', 20)->nullable();
            $table->string('build', 20)->nullable();
            $table->string('status', 20)->nullable();
            $table->bigInteger('exp')->default(0);
            $table->double('ehp')->default(0);
            $table->double('ehb')->default(0);
            $table->double('ttm')->default(0);
            $table->double('tt200m')->default(0);
            $table->unsignedSmallInteger('combat_level')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('wom_updated_at')->nullable();
            $table->timestamp('last_changed_at')->nullable();
            $table->timestamp('update_requested_at')->nullable();
            $table->timestamp('backfilled_at')->nullable();
            $table->timestamp('not_found_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->boolean('tracking_opt_in')->default(false);
            $table->boolean('show_on_progress')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wom_players');
    }
};
