<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A screenshot the owner hid is only shown to them and to instance admins;
     * the event itself stays in the feed. `hide_new_screenshots` is the owner's
     * per-account default for screenshots the plugin uploads from now on.
     */
    public function up(): void
    {
        Schema::table('feed_events', function (Blueprint $table) {
            $table->boolean('screenshot_hidden')->default(false);
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->boolean('hide_new_screenshots')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('feed_events', function (Blueprint $table) {
            $table->dropColumn('screenshot_hidden');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('hide_new_screenshots');
        });
    }
};
