<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Who installed this pack from the community hub. Null for the bundled vanilla
     * pack and owner/admin-installed (instance-managed) packs, which don't count
     * against a member's personal install quota. Nulled on the user's deletion so
     * the pack itself survives.
     */
    public function up(): void
    {
        Schema::table('resource_packs', function (Blueprint $table) {
            $table->foreignId('installed_by_user_id')
                ->nullable()
                ->after('dark_mode')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('resource_packs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('installed_by_user_id');
        });
    }
};
