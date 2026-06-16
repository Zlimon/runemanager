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
            // Optional clean screenshot of the moment, uploaded by the plugin and
            // attached to the matching event (SPEC §8). Stored on the public disk.
            $table->string('screenshot_path')->nullable()->after('payload');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feed_events', function (Blueprint $table) {
            $table->dropColumn('screenshot_path');
        });
    }
};
