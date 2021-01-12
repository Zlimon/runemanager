<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->integer('category_id')->unsigned();
            $table->string('name')->unique();
            $table->string('alias');
            $table->string('model');
        });

        /**
         * Categories:
         * 1 - skill
         * 2 - boss
         * 3 - raid
         * 4 - npc
         * 5 - clue
         * 6 - minigame
         * 7 - other
         * 8 - account
         */

        DB::table('collections')->insert([
            ["category_id" => 2, "name" => "abyssal sire", "alias" => "Abyssal Sire", "model" => "App\Boss\AbyssalSire"],
            ["category_id" => 2, "name" => "alchemical hydra", "alias" => "Alchemical Hydra", "model" => "App\Boss\AlchemicalHydra"],
            ["category_id" => 2, "name" => "barrows chests", "alias" => "Barrows Chests", "model" => "App\Boss\BarrowsChests"],
            ["category_id" => 2, "name" => "bryophyta", "alias" => "Bryophyta", "model" => "App\Boss\Bryophyta"],
            ["category_id" => 2, "name" => "callisto", "alias" => "Callisto", "model" => "App\Boss\Callisto"],
            ["category_id" => 2, "name" => "cerberus", "alias" => "Cerberus", "model" => "App\Boss\Cerberus"],
            ["category_id" => 2, "name" => "chaos elemental", "alias" => "Chaos Elemental", "model" => "App\Boss\ChaosElemental"],
            ["category_id" => 2, "name" => "chaos fanatic", "alias" => "Chaos Fanatic", "model" => "App\Boss\ChaosFanatic"],
            ["category_id" => 2, "name" => "commander zilyana", "alias" => "Commander Zilyana", "model" => "App\Boss\CommanderZilyana"],
            ["category_id" => 2, "name" => "corporeal beast", "alias" => "Corporeal Beast", "model" => "App\Boss\CorporealBeast"],
            ["category_id" => 2, "name" => "crazy archaeologist", "alias" => "Crazy Archaeologist", "model" => "App\Boss\CrazyArchaeologist"],
            ["category_id" => 2, "name" => "dagannoth kings", "alias" => "Dagannoth Kings", "model" => "App\Boss\DagannothKings"],
            ["category_id" => 2, "name" => "dagannoth prime", "alias" => "Dagannoth Prime", "model" => "App\Boss\DagannothPrime"],
            ["category_id" => 2, "name" => "dagannoth rex", "alias" => "Dagannoth Rex", "model" => "App\Boss\DagannothRex"],
            ["category_id" => 2, "name" => "dagannoth supreme", "alias" => "Dagannoth Surpeme", "model" => "App\Boss\DagannothSupreme"],
            ["category_id" => 2, "name" => "deranged archaeologist", "alias" => "Deranged Archaeologist", "model" => "App\Boss\DerangedArchaeologist"],
            ["category_id" => 2, "name" => "general graardor", "alias" => "General Graardor", "model" => "App\Boss\GeneralGraardor"],
            ["category_id" => 2, "name" => "giant mole", "alias" => "Giant Mole", "model" => "App\Boss\GiantMole"],
            ["category_id" => 2, "name" => "grotesque guardians", "alias" => "Grotesque Guardians", "model" => "App\Boss\GrotesqueGuardians"],
            ["category_id" => 2, "name" => "hespori", "alias" => "Hespori", "model" => "App\Boss\Hespori"],
            ["category_id" => 2, "name" => "kalphite queen", "alias" => "Kalphite Queen", "model" => "App\Boss\KalphiteQueen"],
            ["category_id" => 2, "name" => "king black dragon", "alias" => "King Black Dragon", "model" => "App\Boss\KingBlackDragon"],
            ["category_id" => 2, "name" => "kraken", "alias" => "Kraken", "model" => "App\Boss\Kraken"],
            ["category_id" => 2, "name" => "kreearra", "alias" => "Kree'arra", "model" => "App\Boss\KreeArra"],
            ["category_id" => 2, "name" => "kril tsutsaroth", "alias" => "K'ril Tsutsaroth", "model" => "App\Boss\KrilTsutsaroth"],
            ["category_id" => 2, "name" => "mimic", "alias" => "Mimic", "model" => "App\Boss\Mimic"],
            ["category_id" => 2, "name" => "obor", "alias" => "Obor", "model" => "App\Boss\Obor"],
            ["category_id" => 2, "name" => "sarachnis", "alias" => "Sarachnis", "model" => "App\Boss\Sarachnis"],
            ["category_id" => 2, "name" => "scorpia", "alias" => "Scorpia", "model" => "App\Boss\Scorpia"],
            ["category_id" => 2, "name" => "skotizo", "alias" => "Skotizo", "model" => "App\Boss\Skotizo"],
            ["category_id" => 2, "name" => "the corrupted gauntlet", "alias" => "The Corrupted Gauntlet", "model" => "App\Boss\TheCorruptedGauntlet"],
            ["category_id" => 2, "name" => "the fight caves", "alias" => "The Fight Caves", "model" => "App\Boss\TheFightCaves"],
            ["category_id" => 2, "name" => "the gauntlet", "alias" => "The Gauntlet", "model" => "App\Boss\TheGauntlet"],
            ["category_id" => 2, "name" => "the inferno", "alias" => "The Inferno", "model" => "App\Boss\TheInferno"],
            ["category_id" => 2, "name" => "the nightmare", "alias" => "The Nightmare", "model" => "App\Boss\TheNightmare"],
            ["category_id" => 2, "name" => "thermonuclear smoke devil", "alias" => "Thermonuclear Smoke Devil", "model" => "App\Boss\ThermonuclearSmokeDevil"],
            ["category_id" => 2, "name" => "venenatis", "alias" => "Venenatis", "model" => "App\Boss\Venenatis"],
            ["category_id" => 2, "name" => "vetion", "alias" => "Vet'ion", "model" => "App\Boss\Vetion"],
            ["category_id" => 2, "name" => "vorkath", "alias" => "Vorkath", "model" => "App\Boss\Vorkath"],
            ["category_id" => 2, "name" => "wintertodt", "alias" => "Wintertodt", "model" => "App\Boss\Wintertodt"],
            ["category_id" => 2, "name" => "zalcano", "alias" => "Zalcano", "model" => "App\Boss\Zalcano"],
            ["category_id" => 2, "name" => "zulrah", "alias" => "Zulrah", "model" => "App\Boss\Zulrah"],

            ["category_id" => 3, "name" => "chambers of xeric", "alias" => "Chambers of Xeric", "model" => "App\Raid\ChambersOfXeric"],
            ["category_id" => 3, "name" => "chambers of xeric challenge mode", "alias" => "COX: Challenge Mode", "model" => "App\Raid\ChambersOfXericChallengeMode"],
            ["category_id" => 3, "name" => "theatre of blood", "alias" => "Theatre of Blood", "model" => "App\Raid\TheatreOfBlood"],

            ["category_id" => 4, "name" => "goblin", "alias" => "Goblin", "model" => "App\Npc\Goblin"], // TODO remove later
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('collections');
    }
}
