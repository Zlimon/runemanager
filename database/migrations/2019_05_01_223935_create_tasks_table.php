<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('task');
            $table->enum('difficulty', ['easy', 'medium', 'hard', 'elite']);
            $table->integer('icon')->nullable();
            $table->string('reward')->nullable();
        });

        DB::table('tasks')->insert(
            [
                ["task" => "Get bolt racks from Barrows", "difficulty" => "easy", "icon" => "4740"],
                ["task" => "Get a Mole claw + skin", "difficulty" => "easy", "icon" => "7416"],
                ["task" => "Get 1 Ancient shard", "difficulty" => "easy", "icon" => "19677"],
                ["task" => "Get a Burnt page", "difficulty" => "easy", "icon" => "20718"],
                ["task" => "Get 1 Unique from Wintertodt", "difficulty" => "easy", "icon" => "20708"],
                ["task" => "Get 1 Unique from Wintertodt", "difficulty" => "easy", "icon" => "20708"],
                ["task" => "Get 1 Unique from Wintertodt", "difficulty" => "easy", "icon" => "20708"],
                ["task" => "Get 1 Unique from Wintertodt", "difficulty" => "easy", "icon" => "20708"],
                ["task" => "Get 5 new uniques from beginner clues", "difficulty" => "easy", "icon" => "23182"],
                ["task" => "Get 5 new uniques from easy clues", "difficulty" => "easy", "icon" => "2677"],
                ["task" => "Get 5 new uniques from easy clues", "difficulty" => "easy", "icon" => "2677"],
                ["task" => "Get 5 new uniques from easy clues", "difficulty" => "easy", "icon" => "2677"],
                ["task" => "Get 5 new uniques from easy clues", "difficulty" => "easy", "icon" => "2677"],
                ["task" => "Get 5 new uniques from easy clues", "difficulty" => "easy", "icon" => "2677"],
                ["task" => "Get 5 new uniques from easy clues", "difficulty" => "easy", "icon" => "2677"],
                ["task" => "Get 5 new uniques from medium clues", "difficulty" => "easy", "icon" => "2801"],
                ["task" => "Get 5 new uniques from medium clues", "difficulty" => "easy", "icon" => "2801"],
                ["task" => "Get 5 new uniques from medium clues", "difficulty" => "easy", "icon" => "2801"],
                ["task" => "Get 5 new uniques from medium clues", "difficulty" => "easy", "icon" => "2801"],
                ["task" => "Get 5 new uniques from hard clues", "difficulty" => "easy", "icon" => "2722"],
                ["task" => "Get 5 new uniques from hard clues", "difficulty" => "easy", "icon" => "2722"],
                ["task" => "Get Runner boots", "difficulty" => "easy", "icon" => "10552"],
                ["task" => "Get Penance gloves", "difficulty" => "easy", "icon" => "10553"],
                ["task" => "Get a Granite body", "difficulty" => "easy", "icon" => "10564"],
                ["task" => "Get 1 new halo", "difficulty" => "easy", "icon" => "12639"],
                ["task" => "Get the full Red decorative set", "difficulty" => "easy", "icon" => "4069"],
                ["task" => "Get the Zamorak hood & cloak", "difficulty" => "easy", "icon" => "4515"],
                ["task" => "Get the Saradomin hood & cloak", "difficulty" => "easy", "icon" => "4513"],
                ["task" => "Get 1 Angler piece", "difficulty" => "easy", "icon" => "13258"],
                ["task" => "Get 1 new unique from Gnome Restaurant", "difficulty" => "easy", "icon" => "9470"],
                ["task" => "Get a Beginner wand", "difficulty" => "easy", "icon" => "6908"],
                ["task" => "Unlock bones to peaches", "difficulty" => "easy", "icon" => "6926"],
                ["task" => "Get Infinity boots", "difficulty" => "easy", "icon" => "6920"],
                ["task" => "Get Infinity gloves", "difficulty" => "easy", "icon" => "6922"],
                ["task" => "Get a Void Knight seal", "difficulty" => "easy", "icon" => "11666"],
                ["task" => "Get Void Knight gloves", "difficulty" => "easy", "icon" => "8842"],
                ["task" => "Get a Void Knight mace", "difficulty" => "easy", "icon" => "8841"],
                ["task" => "Get 1 piece of Rogue equipment", "difficulty" => "easy", "icon" => "5554"],
                ["task" => "Get 1 piece of Rogue equipment", "difficulty" => "easy", "icon" => "5554"],
                ["task" => "Get a Fine cloth", "difficulty" => "easy", "icon" => "3470"],
                ["task" => "Get 1 piece of Lumberjack equipment", "difficulty" => "easy", "icon" => "10941"],
                ["task" => "Get the Gricoller's can", "difficulty" => "easy", "icon" => "13353"],
                ["task" => "Get 1 piece of Farmer's equipment", "difficulty" => "easy", "icon" => "13646"],
                ["task" => "Get Blue Rum", "difficulty" => "easy", "icon" => "8941"],
                ["task" => "Get Red Rum", "difficulty" => "easy", "icon" => "8940"],
                ["task" => "Get The stuff", "difficulty" => "easy", "icon" => "8988"],
                ["task" => "Get the Pearl fishing rod", "difficulty" => "easy", "icon" => "22846"],
                ["task" => "Get 1 unique Champion scroll", "difficulty" => "easy", "icon" => "6798"],
                ["task" => "Get the Marksman headpiece", "difficulty" => "easy", "icon" => "2983"],
                ["task" => "Get a Tea flask", "difficulty" => "easy", "icon" => "10859"],
                ["task" => "Get a Rune satchel", "difficulty" => "easy", "icon" => "10882"],
                ["task" => "Get a Green satchel", "difficulty" => "easy", "icon" => "10878"],
                ["task" => "Get a Red satchel", "difficulty" => "easy", "icon" => "10879"],
                ["task" => "Get a Black satchel", "difficulty" => "easy", "icon" => "10880"],
                ["task" => "Get a Rune defender", "difficulty" => "easy", "icon" => "8850"],
                ["task" => "Get 1 unique note from Fossil Island", "difficulty" => "easy", "icon" => "1508"],
                ["task" => "Get 1 unique note from Fossil Island", "difficulty" => "easy", "icon" => "1508"],
                ["task" => "Get 1 piece of Prospector equipment", "difficulty" => "easy", "icon" => "12013"],
                ["task" => "Get 1 piece of Prospector equipment", "difficulty" => "easy", "icon" => "12013"],
                ["task" => "Get 1 piece of Prospector equipment", "difficulty" => "easy", "icon" => "12013"],
                ["task" => "Get 1 piece of Prospector equipment", "difficulty" => "easy", "icon" => "12013"],
                ["task" => "Get 2 unique Ancient pages", "difficulty" => "easy", "icon" => "11341"],
                ["task" => "Get 2 unique Ancient pages", "difficulty" => "easy", "icon" => "11341"],
                ["task" => "Get Revenant ether", "difficulty" => "easy", "icon" => "21820"],
                ["task" => "Get 1 piece of Graceful equipment", "difficulty" => "easy", "icon" => "11850"],
                ["task" => "Get 1 piece of Graceful equipment", "difficulty" => "easy", "icon" => "11850"],
                ["task" => "Get 1 piece of Graceful equipment", "difficulty" => "easy", "icon" => "11850"],
                ["task" => "Get a Crawling hand", "difficulty" => "easy", "icon" => "7975"],
                ["task" => "Get a Cockatrice head", "difficulty" => "easy", "icon" => "7976"],
                ["task" => "Get 1 unique from Mogres", "difficulty" => "easy", "icon" => "6665"],
                ["task" => "Get 1 unique from Mogres", "difficulty" => "easy", "icon" => "6665"],
                ["task" => "Get a Brine sabre", "difficulty" => "easy", "icon" => "11037"],
                ["task" => "Get a Leaf-bladed sword", "difficulty" => "easy", "icon" => "11902"],
                ["task" => "Get a Black mask", "difficulty" => "easy", "icon" => "8901"],
                ["task" => "Get the next tier of metal boots", "difficulty" => "easy", "icon" => "4119"],
                ["task" => "Get the next tier of metal boots", "difficulty" => "easy", "icon" => "4119"],
                ["task" => "Get the next tier of metal boots", "difficulty" => "easy", "icon" => "4119"],
                ["task" => "Get the next tier of metal boots", "difficulty" => "easy", "icon" => "4119"],
                ["task" => "Get the next tier of metal boots", "difficulty" => "easy", "icon" => "4119"],
                ["task" => "Get a Mystic hat (light)", "difficulty" => "easy", "icon" => "4109"],
                ["task" => "Get a Mystic robe bottom (light)", "difficulty" => "easy", "icon" => "4113"],
                ["task" => "Get Mystic gloves (light)", "difficulty" => "easy", "icon" => "4115"],
                ["task" => "Get Mystic boots (light)", "difficulty" => "easy", "icon" => "4117"],
                ["task" => "Get a Mystic hat (dark)", "difficulty" => "easy", "icon" => "4099"],
                ["task" => "Get Mystic gloves (dark)", "difficulty" => "easy", "icon" => "4105"],
                ["task" => "Get Mystic boots (dark)", "difficulty" => "easy", "icon" => "4107"],
                ["task" => "Get 1 unique Tzhaar drop", "difficulty" => "easy", "icon" => "6568"],
                ["task" => "Get 1 unique Tzhaar drop", "difficulty" => "easy", "icon" => "6568"],
                ["task" => "Get a Big swordfish", "difficulty" => "easy", "icon" => "7991"],
                ["task" => "Get a Big bass", "difficulty" => "easy", "icon" => "7989"],
                ["task" => "Get a Long bone", "difficulty" => "easy", "icon" => "10976"],
                ["task" => "Get an Ecumenical key", "difficulty" => "easy", "icon" => "11942"],
                ["task" => "Get 1 unique Dark totem piece", "difficulty" => "easy", "icon" => "19679"],
                ["task" => "Get Mining gloves", "difficulty" => "easy", "icon" => "21343"],
                ["task" => "Get a Right skull half", "difficulty" => "easy", "icon" => "9007"],
                ["task" => "Get a Left skull half", "difficulty" => "easy", "icon" => "9008"],
                ["task" => "Get a Top of sceptre", "difficulty" => "easy", "icon" => "9010"],
                ["task" => "Get a Bottom of sceptre", "difficulty" => "easy", "icon" => "9011"],
                ["task" => "Get a Mossy key", "difficulty" => "easy", "icon" => "22374"],
                ["task" => "Get a Giant key", "difficulty" => "easy", "icon" => "20754"],
                ["task" => "Get a Fresh crab claw & Fresh crab shell", "difficulty" => "easy", "icon" => "7536"],
                ["task" => "Get a Xeric's talisman (inert)", "difficulty" => "easy", "icon" => "13392"],
                ["task" => "Complete the Ardougne Easy Diary", "difficulty" => "easy", "icon" => "13121"],
                ["task" => "Complete the Desert Easy Diary", "difficulty" => "easy", "icon" => "13133"],
                ["task" => "Complete the Falador Easy Diary", "difficulty" => "easy", "icon" => "13117"],
                ["task" => "Complete the Fremennik Easy Diary", "difficulty" => "easy", "icon" => "13129"],
                ["task" => "Complete the Kandarin Easy Diary", "difficulty" => "easy", "icon" => "13137"],
                ["task" => "Complete the Karamja Easy Diary", "difficulty" => "easy", "icon" => "11136"],
                ["task" => "Complete the Kourend&Kebos Easy Diary", "difficulty" => "easy", "icon" => "22941"],
                ["task" => "Complete the Lumbridge&Draynor Easy Diary", "difficulty" => "easy", "icon" => "13125"],
                ["task" => "Complete the Morytania Easy Diary", "difficulty" => "easy", "icon" => "13112"],
                ["task" => "Complete the Varrock Easy Diary", "difficulty" => "easy", "icon" => "13104"],
                ["task" => "Complete the Western Provinces Easy Diary", "difficulty" => "easy", "icon" => "13141"],
                ["task" => "Complete the Wilderness Easy Diary", "difficulty" => "easy", "icon" => "13108"]
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
        Schema::dropIfExists('tasks');
    }
}
