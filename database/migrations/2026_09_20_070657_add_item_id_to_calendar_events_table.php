<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SPEC §10 — an event can carry an OSRS item as its icon (the raid it's for,
     * the drop it's hunting). Stores the item id only; name/icon are resolved
     * from the static MongoDB item catalogue at render time.
     */
    public function up(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->unsignedInteger('item_id')->nullable()->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropColumn('item_id');
        });
    }
};
