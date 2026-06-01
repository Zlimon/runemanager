<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resource_packs', function (Blueprint $table) {
            // DaisyUI theme name to apply when this pack is active. Nullable: a pack
            // without a theme falls back to the instance default 'runemanager'.
            $table->string('daisyui_theme')->nullable()->after('dark_mode');
        });

        // Backfill: the existing sample-vanilla pack maps to the 'runemanager' theme
        // defined in resources/css/app.css (default: true).
        DB::table('resource_packs')
            ->where('name', 'sample-vanilla')
            ->update(['daisyui_theme' => 'runemanager']);
    }

    public function down(): void
    {
        Schema::table('resource_packs', function (Blueprint $table) {
            $table->dropColumn('daisyui_theme');
        });
    }
};
