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
        Schema::table('users', function (Blueprint $table) {
            // Explicit "no resource pack" preference — plain DaisyUI, no textures.
            // Distinct from resource_pack_id = null (which means "follow the
            // instance default", falling through to Default Vanilla).
            $table->boolean('disable_resource_pack')->default(false)->after('resource_pack_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('disable_resource_pack');
        });
    }
};
