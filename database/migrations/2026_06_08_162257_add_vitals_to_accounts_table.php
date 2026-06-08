<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Live status-orb values pushed by the plugin: current/max hitpoints and
     * prayer, run energy (0-100) and special attack (0-100). Nullable until the
     * plugin first pushes them.
     */
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->unsignedSmallInteger('hitpoints')->nullable();
            $table->unsignedSmallInteger('hitpoints_max')->nullable();
            $table->unsignedSmallInteger('prayer')->nullable();
            $table->unsignedSmallInteger('prayer_max')->nullable();
            $table->unsignedTinyInteger('run_energy')->nullable();
            $table->unsignedTinyInteger('special_attack')->nullable();
            $table->timestamp('vitals_updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn([
                'hitpoints', 'hitpoints_max', 'prayer', 'prayer_max',
                'run_energy', 'special_attack', 'vitals_updated_at',
            ]);
        });
    }
};
