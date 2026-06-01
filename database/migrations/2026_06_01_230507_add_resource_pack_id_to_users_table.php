<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Per-user resource pack override. NULL = use the instance default
            // from settings.resource_pack_id (set by `resourcepack:switch`).
            $table->foreignId('resource_pack_id')
                ->nullable()
                ->after('icon_id')
                ->constrained('resource_packs')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['resource_pack_id']);
            $table->dropColumn('resource_pack_id');
        });
    }
};
