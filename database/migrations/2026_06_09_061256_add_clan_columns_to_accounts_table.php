<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SPEC §5.2 — the in-game clan rank pushed by the plugin. `clan_rank` holds
     * RuneLite's ClanRank value (GUEST=-1, custom 1–99, ADMINISTRATOR=100,
     * DEPUTY_OWNER=125, OWNER=126, JMOD=127); `clan_title` is the custom rank
     * name shown in-game.
     */
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('clan_name')->nullable()->after('account_type');
            $table->smallInteger('clan_rank')->nullable()->after('clan_name');
            $table->string('clan_title')->nullable()->after('clan_rank');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['clan_name', 'clan_rank', 'clan_title']);
        });
    }
};
