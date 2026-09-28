<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Promote the plugin-supplied event position out of the jsonb payload so
     * the event map can select positioned events in portable SQL. Kept in sync
     * by FeedEvent's saving hook; existing rows are filled by the follow-up
     * backfill migration, outside this one's ALTER lock.
     *
     * The index serves the map's read: a handful of accounts, newest first.
     */
    public function up(): void
    {
        Schema::table('feed_events', function (Blueprint $table) {
            $table->integer('world_x')->nullable();
            $table->integer('world_y')->nullable();
            $table->smallInteger('world_plane')->nullable();
            $table->string('location', 80)->nullable();

            $table->index(['account_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::table('feed_events', function (Blueprint $table) {
            $table->dropIndex(['account_id', 'occurred_at']);
            $table->dropColumn(['world_x', 'world_y', 'world_plane', 'location']);
        });
    }
};
