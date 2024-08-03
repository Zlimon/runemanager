<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
        });

        DB::table('categories')->insert(
            [
                ['name' => 'Skill', 'slug' => 'skill'],
                ['name' => 'PvP', 'slug' => 'pvp'],
                ['name' => 'Clue', 'slug' => 'clue'],
                ['name' => 'Minigame', 'slug' => 'minigame'],
                ['name' => 'Boss', 'slug' => 'boss'],
                ['name' => 'Raid', 'slug' => 'raid'],
                ['name' => 'Other', 'slug' => 'other'],
                ['name' => 'NPC', 'slug' => 'npc'],
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
}
