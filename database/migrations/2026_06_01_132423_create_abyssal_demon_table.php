<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abyssal_demon', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('account_id')->unique();
            $table->unsignedInteger('kill_count')->default(0);
            $table->unsignedInteger('obtained')->default(0);
            $table->unsignedInteger('abyssal_ashes')->default(0);
            $table->unsignedInteger('air_rune')->default(0);
            $table->unsignedInteger('chaos_rune')->default(0);
            $table->unsignedInteger('blood_rune')->default(0);
            $table->unsignedInteger('law_rune')->default(0);
            $table->unsignedInteger('grimy_guam_leaf')->default(0);
            $table->unsignedInteger('grimy_marrentill')->default(0);
            $table->unsignedInteger('grimy_tarromin')->default(0);
            $table->unsignedInteger('grimy_harralander')->default(0);
            $table->unsignedInteger('grimy_ranarr_weed')->default(0);
            $table->unsignedInteger('grimy_irit_leaf')->default(0);
            $table->unsignedInteger('black_sword')->default(0);
            $table->unsignedInteger('grimy_avantoe')->default(0);
            $table->unsignedInteger('grimy_kwuarm')->default(0);
            $table->unsignedInteger('grimy_cadantine')->default(0);
            $table->unsignedInteger('grimy_lantadyme')->default(0);
            $table->unsignedInteger('grimy_dwarf_weed')->default(0);
            $table->unsignedInteger('pure_essence')->default(0);
            $table->unsignedInteger('adamantite_bar')->default(0);
            $table->unsignedInteger('coins')->default(0);
            $table->unsignedInteger('steel_battleaxe')->default(0);
            $table->unsignedInteger('lobster')->default(0);
            $table->unsignedInteger('cosmic_talisman')->default(0);
            $table->unsignedInteger('chaos_talisman')->default(0);
            $table->unsignedInteger('defence_potion(3)')->default(0);
            $table->unsignedInteger('black_axe')->default(0);
            $table->unsignedInteger('mithril_kiteshield')->default(0);
            $table->unsignedInteger('rune_chainbody')->default(0);
            $table->unsignedInteger('ensouled_abyssal_head')->default(0);
            $table->unsignedInteger('brimstone_key')->default(0);
            $table->unsignedInteger('clue_scroll(hard)')->default(0);
            $table->unsignedInteger('clue_scroll(elite)')->default(0);
            $table->unsignedInteger('abyssal_head')->default(0);
            $table->unsignedInteger('ancient_shard')->default(0);
            $table->unsignedInteger('dark_totem_base')->default(0);
            $table->unsignedInteger('dark_totem_middle')->default(0);
            $table->unsignedInteger('rune_med_helm')->default(0);
            $table->unsignedInteger('dark_totem_top')->default(0);
            $table->unsignedInteger('abyssal_whip')->default(0);
            $table->unsignedInteger('abyssal_dagger')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abyssal_demon');
    }
};