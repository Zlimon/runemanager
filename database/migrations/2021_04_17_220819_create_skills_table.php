<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSkillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->integer('category_id')->unsigned();
            $table->integer('order')->unsigned()->unique();
            $table->string('name')->unique();
            $table->string('slug');
            $table->string('model');
        });

        DB::table('skills')->insert(
            [
                [
                    'category_id' => 1,
                    'order' => 1,
                    'name' => 'Attack',
                    'slug' => 'attack',
                    'model' => 'App\Models\Skill\Attack'
                ],
                [
                    'category_id' => 1,
                    'order' => 2,
                    'name' => 'Defence',
                    'slug' => 'defence',
                    'model' => 'App\Models\Skill\Defence'
                ],
                [
                    'category_id' => 1,
                    'order' => 3,
                    'name' => 'Strength',
                    'slug' => 'strength',
                    'model' => 'App\Models\Skill\Strength'
                ],
                [
                    'category_id' => 1,
                    'order' => 4,
                    'name' => 'Hitpoints',
                    'slug' => 'hitpoints',
                    'model' => 'App\Models\Skill\Hitpoints'
                ],
                [
                    'category_id' => 1,
                    'order' => 5,
                    'name' => 'Ranged',
                    'slug' => 'ranged',
                    'model' => 'App\Models\Skill\Ranged'
                ],
                [
                    'category_id' => 1,
                    'order' => 6,
                    'name' => 'Prayer',
                    'slug' => 'prayer',
                    'model' => 'App\Models\Skill\Prayer'
                ],
                [
                    'category_id' => 1,
                    'order' => 7,
                    'name' => 'Magic',
                    'slug' => 'magic',
                    'model' => 'App\Models\Skill\Magic'
                ],
                [
                    'category_id' => 1,
                    'order' => 8,
                    'name' => 'Cooking',
                    'slug' => 'cooking',
                    'model' => 'App\Models\Skill\Cooking'
                ],
                [
                    'category_id' => 1,
                    'order' => 9,
                    'name' => 'Woodcutting',
                    'slug' => 'woodcutting',
                    'model' => 'App\Models\Skill\Woodcutting'
                ],
                [
                    'category_id' => 1,
                    'order' => 10,
                    'name' => 'Fletching',
                    'slug' => 'fletching',
                    'model' => 'App\Models\Skill\Fletching'
                ],
                [
                    'category_id' => 1,
                    'order' => 11,
                    'name' => 'Fishing',
                    'slug' => 'fishing',
                    'model' => 'App\Models\Skill\Fishing'
                ],
                [
                    'category_id' => 1,
                    'order' => 12,
                    'name' => 'Firemaking',
                    'slug' => 'firemaking',
                    'model' => 'App\Models\Skill\Firemaking'
                ],
                [
                    'category_id' => 1,
                    'order' => 13,
                    'name' => 'Crafting',
                    'slug' => 'crafting',
                    'model' => 'App\Models\Skill\Crafting'
                ],
                [
                    'category_id' => 1,
                    'order' => 14,
                    'name' => 'Smithing',
                    'slug' => 'smithing',
                    'model' => 'App\Models\Skill\Smithing'
                ],
                [
                    'category_id' => 1,
                    'order' => 15,
                    'name' => 'Mining',
                    'slug' => 'mining',
                    'model' => 'App\Models\Skill\Mining'
                ],
                [
                    'category_id' => 1,
                    'order' => 16,
                    'name' => 'Herblore',
                    'slug' => 'herblore',
                    'model' => 'App\Models\Skill\Herblore'
                ],
                [
                    'category_id' => 1,
                    'order' => 17,
                    'name' => 'Agility',
                    'slug' => 'agility',
                    'model' => 'App\Models\Skill\Agility'
                ],
                [
                    'category_id' => 1,
                    'order' => 18,
                    'name' => 'Thieving',
                    'slug' => 'thieving',
                    'model' => 'App\Models\Skill\Thieving'
                ],
                [
                    'category_id' => 1,
                    'order' => 19,
                    'name' => 'Slayer',
                    'slug' => 'slayer',
                    'model' => 'App\Models\Skill\Slayer'
                ],
                [
                    'category_id' => 1,
                    'order' => 20,
                    'name' => 'Farming',
                    'slug' => 'farming',
                    'model' => 'App\Models\Skill\Farming'
                ],
                [
                    'category_id' => 1,
                    'order' => 21,
                    'name' => 'Runecraft',
                    'slug' => 'runecraft',
                    'model' => 'App\Models\Skill\Runecraft'
                ],
                [
                    'category_id' => 1,
                    'order' => 22,
                    'name' => 'Hunter',
                    'slug' => 'hunter',
                    'model' => 'App\Models\Skill\Hunter'
                ],
                [
                    'category_id' => 1,
                    'order' => 23,
                    'name' => 'Construction',
                    'slug' => 'construction',
                    'model' => 'App\Models\Skill\Construction'
                ]
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
        Schema::dropIfExists('skills');
    }
}
