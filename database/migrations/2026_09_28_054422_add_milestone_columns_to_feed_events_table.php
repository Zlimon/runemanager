<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make the Adventurer's Log selection queryable on both PostgreSQL and
     * SQLite: comparing a jsonb payload field is not portable, so the
     * milestone flag and the loot value get their own columns, kept in sync by
     * FeedEvent's saving hook. Existing rows are classified by the follow-up
     * backfill migration, outside this one's ALTER lock.
     */
    public function up(): void
    {
        Schema::table('feed_events', function (Blueprint $table) {
            $table->boolean('milestone')->default(false);
            $table->unsignedBigInteger('value')->nullable();

            $table->index(['account_id', 'milestone', 'occurred_at']);
            $table->index(['account_id', 'type', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::table('feed_events', function (Blueprint $table) {
            $table->dropIndex(['account_id', 'milestone', 'occurred_at']);
            $table->dropIndex(['account_id', 'type', 'occurred_at']);
            $table->dropColumn(['milestone', 'value']);
        });
    }
};
