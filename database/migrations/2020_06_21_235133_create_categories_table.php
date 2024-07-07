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
            $table->string('category');
        });

        DB::table('categories')->insert(
            [
                ['category' => 'skill'],
                ['category' => 'pvp'],
                ['category' => 'clue'],
                ['category' => 'minigame'],
                ['category' => 'boss'],
                ['category' => 'raid'],
                ['category' => 'npc'],
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
