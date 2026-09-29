<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Also clears `backfilled_at`, so the next `wom:sync` re-backfills every
     * linked account once and fills in the per-metric maps of its history.
     */
    public function up(): void
    {
        Schema::table('wom_players', function (Blueprint $table) {
            $table->timestamp('milestones_synced_at')->nullable()->after('backfilled_at');
        });

        DB::table('wom_players')->update(['backfilled_at' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wom_players', function (Blueprint $table) {
            $table->dropColumn('milestones_synced_at');
        });
    }
};
