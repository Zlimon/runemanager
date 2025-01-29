<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

        DB::table("skills")->insert(
            [
                [
                    'category_id' => 1,
                    'order' => 1,
                    'name' => 'Attack',
                    'slug' => 'attack',
                    'model' => 'App\Skill\Attack'
                ],
                [
                    'category_id' => 1,
                    'order' => 2,
                    'name' => 'Defence',
                    'slug' => 'defence',
                    'model' => 'App\Skill\Defence'
                ],
                [
                    'category_id' => 1,
                    'order' => 3,
                    'name' => 'Strength',
                    'slug' => 'strength',
                    'model' => 'App\Skill\Strength'
                ],
                [
                    'category_id' => 1,
                    'order' => 4,
                    'name' => 'Hitpoints',
                    'slug' => 'hitpoints',
                    'model' => 'App\Skill\Hitpoints'
                ],
                [
                    'category_id' => 1,
                    'order' => 5,
                    'name' => 'Ranged',
                    'slug' => 'ranged',
                    'model' => 'App\Skill\Ranged'
                ],
                [
                    'category_id' => 1,
                    'order' => 6,
                    'name' => 'Prayer',
                    'slug' => 'prayer',
                    'model' => 'App\Skill\Prayer'
                ],
                [
                    'category_id' => 1,
                    'order' => 7,
                    'name' => 'Magic',
                    'slug' => 'magic',
                    'model' => 'App\Skill\Magic'
                ],
                [
                    'category_id' => 1,
                    'order' => 8,
                    'name' => 'Cooking',
                    'slug' => 'cooking',
                    'model' => 'App\Skill\Cooking'
                ],
                [
                    'category_id' => 1,
                    'order' => 9,
                    'name' => 'Woodcutting',
                    'slug' => 'woodcutting',
                    'model' => 'App\Skill\Woodcutting'
                ],
                [
                    'category_id' => 1,
                    'order' => 10,
                    'name' => 'Fletching',
                    'slug' => 'fletching',
                    'model' => 'App\Skill\Fletching'
                ],
                [
                    'category_id' => 1,
                    'order' => 11,
                    'name' => 'Fishing',
                    'slug' => 'fishing',
                    'model' => 'App\Skill\Fishing'
                ],
                [
                    'category_id' => 1,
                    'order' => 12,
                    'name' => 'Firemaking',
                    'slug' => 'firemaking',
                    'model' => 'App\Skill\Firemaking'
                ],
                [
                    'category_id' => 1,
                    'order' => 13,
                    'name' => 'Crafting',
                    'slug' => 'crafting',
                    'model' => 'App\Skill\Crafting'
                ],
                [
                    'category_id' => 1,
                    'order' => 14,
                    'name' => 'Smithing',
                    'slug' => 'smithing',
                    'model' => 'App\Skill\Smithing'
                ],
                [
                    'category_id' => 1,
                    'order' => 15,
                    'name' => 'Mining',
                    'slug' => 'mining',
                    'model' => 'App\Skill\Mining'
                ],
                [
                    'category_id' => 1,
                    'order' => 16,
                    'name' => 'Herblore',
                    'slug' => 'herblore',
                    'model' => 'App\Skill\Herblore'
                ],
                [
                    'category_id' => 1,
                    'order' => 17,
                    'name' => 'Agility',
                    'slug' => 'agility',
                    'model' => 'App\Skill\Agility'
                ],
                [
                    'category_id' => 1,
                    'order' => 18,
                    'name' => 'Thieving',
                    'slug' => 'thieving',
                    'model' => 'App\Skill\Thieving'
                ],
                [
                    'category_id' => 1,
                    'order' => 19,
                    'name' => 'Slayer',
                    'slug' => 'slayer',
                    'model' => 'App\Skill\Slayer'
                ],
                [
                    'category_id' => 1,
                    'order' => 20,
                    'name' => 'Farming',
                    'slug' => 'farming',
                    'model' => 'App\Skill\Farming'
                ],
                [
                    'category_id' => 1,
                    'order' => 21,
                    'name' => 'Runecraft',
                    'slug' => 'runecraft',
                    'model' => 'App\Skill\Runecraft'
                ],
                [
                    'category_id' => 1,
                    'order' => 22,
                    'name' => 'Hunter',
                    'slug' => 'hunter',
                    'model' => 'App\Skill\Hunter'
                ],
                [
                    'category_id' => 1,
                    'order' => 23,
                    'name' => 'Construction',
                    'slug' => 'construction',
                    'model' => 'App\Skill\Construction'
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
