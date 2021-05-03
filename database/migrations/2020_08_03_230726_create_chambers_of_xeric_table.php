<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChambersOfXericTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chambers_of_xeric', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained();
            $table->integer('kill_count')->default(0)->unsigned();
            $table->integer('rank')->default(0)->unsigned();
            $table->integer('obtained')->default(0)->unsigned();
            $table->integer('olmlet')->default(0)->unsigned();
            $table->integer('metamorphic_dust')->default(0)->unsigned();
            $table->integer('twisted_bow')->default(0)->unsigned();
            $table->integer('elder_maul')->default(0)->unsigned();
            $table->integer('kodai_insignia')->default(0)->unsigned();
            $table->integer('dragon_claws')->default(0)->unsigned();
            $table->integer('ancestral_hat')->default(0)->unsigned();
            $table->integer('ancestral_robe_top')->default(0)->unsigned();
            $table->integer('ancestral_robe_bottom')->default(0)->unsigned();
            $table->integer('dinhs_bulwark')->default(0)->unsigned();
            $table->integer('dexterous_prayer_scroll')->default(0)->unsigned();
            $table->integer('arcane_prayer_scroll')->default(0)->unsigned();
            $table->integer('dragon_hunter_crossbow')->default(0)->unsigned();
            $table->integer('twisted_buckler')->default(0)->unsigned();
            $table->integer('torn_prayer_scroll')->default(0)->unsigned();
            $table->integer('dark_relic')->default(0)->unsigned();
            $table->integer('onyx')->default(0)->unsigned();
            $table->integer('twisted_ancestral_colour_kit')->default(0)->unsigned();
            $table->integer('xerics_guard')->default(0)->unsigned();
            $table->integer('xerics_warrior')->default(0)->unsigned();
            $table->integer('xerics_sentinel')->default(0)->unsigned();
            $table->integer('xerics_general')->default(0)->unsigned();
            $table->integer('xerics_champion')->default(0)->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chambers_of_xeric');
    }
}
