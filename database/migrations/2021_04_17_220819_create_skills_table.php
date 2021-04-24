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
            $table->string('name');
            $table->string('alias')->unique();
            $table->string('model');
        });

        DB::table('skills')->insert(
            [
                [
                    "category_id" => 1,
                    "order" => 1,
                    "name" => "attack",
                    "alias" => "Attack",
                    "model" => "App\Skill\Attack"
                ],
                [
                    "category_id" => 1,
                    "order" => 2,
                    "name" => "defence",
                    "alias" => "Defence",
                    "model" => "App\Skill\Defence"
                ],
                [
                    "category_id" => 1,
                    "order" => 3,
                    "name" => "strength",
                    "alias" => "Strength",
                    "model" => "App\Skill\Strength"
                ],
                [
                    "category_id" => 1,
                    "order" => 4,
                    "name" => "hitpoints",
                    "alias" => "Hitpoints",
                    "model" => "App\Skill\Hitpoints"
                ],
                [
                    "category_id" => 1,
                    "order" => 5,
                    "name" => "ranged",
                    "alias" => "Ranged",
                    "model" => "App\Skill\Ranged"
                ],
                [
                    "category_id" => 1,
                    "order" => 6,
                    "name" => "prayer",
                    "alias" => "Prayer",
                    "model" => "App\Skill\Prayer"
                ],
                [
                    "category_id" => 1,
                    "order" => 7,
                    "name" => "magic",
                    "alias" => "Magic",
                    "model" => "App\Skill\Magic"
                ],
                [
                    "category_id" => 1,
                    "order" => 8,
                    "name" => "cooking",
                    "alias" => "Cooking",
                    "model" => "App\Skill\Cooking"
                ],
                [
                    "category_id" => 1,
                    "order" => 9,
                    "name" => "woodcutting",
                    "alias" => "Woodcutting",
                    "model" => "App\Skill\Woodcutting"
                ],
                [
                    "category_id" => 1,
                    "order" => 10,
                    "name" => "fletching",
                    "alias" => "Fletching",
                    "model" => "App\Skill\Fletching"
                ],
                [
                    "category_id" => 1,
                    "order" => 11,
                    "name" => "fishing",
                    "alias" => "Fishing",
                    "model" => "App\Skill\Fishing"
                ],
                [
                    "category_id" => 1,
                    "order" => 12,
                    "name" => "firemaking",
                    "alias" => "Firemaking",
                    "model" => "App\Skill\Firemaking"
                ],
                [
                    "category_id" => 1,
                    "order" => 13,
                    "name" => "crafting",
                    "alias" => "Crafting",
                    "model" => "App\Skill\Crafting"
                ],
                [
                    "category_id" => 1,
                    "order" => 14,
                    "name" => "smithing",
                    "alias" => "Smithing",
                    "model" => "App\Skill\Smithing"
                ],
                [
                    "category_id" => 1,
                    "order" => 15,
                    "name" => "mining",
                    "alias" => "Mining",
                    "model" => "App\Skill\Mining"
                ],
                [
                    "category_id" => 1,
                    "order" => 16,
                    "name" => "herblore",
                    "alias" => "Herblore",
                    "model" => "App\Skill\Herblore"
                ],
                [
                    "category_id" => 1,
                    "order" => 17,
                    "name" => "agility",
                    "alias" => "Agility",
                    "model" => "App\Skill\Agility"
                ],
                [
                    "category_id" => 1,
                    "order" => 18,
                    "name" => "thieving",
                    "alias" => "Thieving",
                    "model" => "App\Skill\Thieving"
                ],
                [
                    "category_id" => 1,
                    "order" => 19,
                    "name" => "slayer",
                    "alias" => "Slayer",
                    "model" => "App\Skill\Slayer"
                ],
                [
                    "category_id" => 1,
                    "order" => 20,
                    "name" => "farming",
                    "alias" => "Farming",
                    "model" => "App\Skill\Farming"
                ],
                [
                    "category_id" => 1,
                    "order" => 21,
                    "name" => "runecraft",
                    "alias" => "Runecraft",
                    "model" => "App\Skill\Runecraft"
                ],
                [
                    "category_id" => 1,
                    "order" => 22,
                    "name" => "hunter",
                    "alias" => "Hunter",
                    "model" => "App\Skill\Hunter"
                ],
                [
                    "category_id" => 1,
                    "order" => 23,
                    "name" => "construction",
                    "alias" => "Construction",
                    "model" => "App\Skill\Construction"
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
