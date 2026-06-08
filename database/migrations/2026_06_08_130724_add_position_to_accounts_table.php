<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Last known in-game position for the Live Map. The plugin pushes the
     * player's WorldPoint while location sharing is enabled; "on the map" is
     * derived from how recent position_updated_at is (see config/runemanager.php),
     * so it expires on its own when sharing stops or the player logs out.
     */
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->integer('world_x')->nullable();
            $table->integer('world_y')->nullable();
            $table->unsignedTinyInteger('world_plane')->nullable();
            $table->timestamp('position_updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['world_x', 'world_y', 'world_plane', 'position_updated_at']);
        });
    }
};
