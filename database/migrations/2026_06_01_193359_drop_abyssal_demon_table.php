<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('abyssal_demon');

        DB::table('migrations')
            ->where('migration', '2026_06_01_132423_create_abyssal_demon_table')
            ->delete();
    }

    public function down(): void
    {
        // No-op. abyssal_demon was a per-NPC table from the legacy npc:create
        // command; collection-log data now lives in the `collection_logs` Mongo
        // collection and is plugin-pushed (SPEC §5.2).
    }
};
