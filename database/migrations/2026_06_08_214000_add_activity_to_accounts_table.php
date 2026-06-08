<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * What the character is currently doing in-game (Discord-plugin style:
     * "Fishing", "Fighting Vorkath", "Idle"), pushed by the plugin. Shown only
     * while the account is online.
     */
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('activity', 60)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('activity');
        });
    }
};
