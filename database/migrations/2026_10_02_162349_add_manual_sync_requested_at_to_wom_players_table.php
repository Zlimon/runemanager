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
        Schema::table('wom_players', function (Blueprint $table) {
            $table->timestamp('manual_sync_requested_at')->nullable()->after('update_requested_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wom_players', function (Blueprint $table) {
            $table->dropColumn('manual_sync_requested_at');
        });
    }
};
