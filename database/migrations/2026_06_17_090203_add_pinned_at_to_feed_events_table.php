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
        Schema::table('feed_events', function (Blueprint $table) {
            // SPEC §8 — the account owner can pin an event to their profile's
            // achievement gallery; the timestamp doubles as the gallery order.
            $table->timestamp('pinned_at')->nullable()->after('screenshot_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feed_events', function (Blueprint $table) {
            $table->dropColumn('pinned_at');
        });
    }
};
