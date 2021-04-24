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
                $table->id();
                $table->integer('category_id')->unsigned();
                $table->integer('order')->unsigned()->unique();
                $table->string('name');
                $table->string('alias')->unique();
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

        DB::table('collections')->insert(
            [
                [
                    "category_id" => 2,
                    "order" => 1,
                    "name" => "abyssal sire",
                    "alias" => "Abyssal Sire",
                    "model" => "App\Boss\AbyssalSire"
                ],
                [
                    "category_id" => 2,
                    "order" => 2,
                    "name" => "alchemical hydra",
                    "alias" => "Alchemical Hydra",
                    "model" => "App\Boss\AlchemicalHydra"
                ],
                [
                    "category_id" => 2,
                    "order" => 3,
                    "name" => "barrows chests",
                    "alias" => "Barrows Chests",
                    "model" => "App\Boss\BarrowsChests"
                ],
                [
                    "category_id" => 2,
                    "order" => 4,
                    "name" => "bryophyta",
                    "alias" => "Bryophyta",
                    "model" => "App\Boss\Bryophyta"
                ],
                [
                    "category_id" => 2,
                    "order" => 5,
                    "name" => "callisto",
                    "alias" => "Callisto",
                    "model" => "App\Boss\Callisto"
                ],
                [
                    "category_id" => 2,
                    "order" => 6,
                    "name" => "cerberus",
                    "alias" => "Cerberus",
                    "model" => "App\Boss\Cerberus"
                ],
                [
                    "category_id" => 3,
                    "order" => 7,
                    "name" => "chambers of xeric",
                    "alias" => "Chambers of Xeric",
                    "model" => "App\Raid\ChambersOfXeric"
                ],
                [
                    "category_id" => 3,
                    "order" => 8,
                    "name" => "chambers of xeric challenge mode",
                    "alias" => "CoX: Challenge Mode",
                    "model" => "App\Raid\ChambersOfXericChallengeMode"
                ],
                [
                    "category_id" => 2,
                    "order" => 9,
                    "name" => "chaos elemental",
                    "alias" => "Chaos Elemental",
                    "model" => "App\Boss\ChaosElemental"
                ],
                [
                    "category_id" => 2,
                    "order" => 10,
                    "name" => "chaos fanatic",
                    "alias" => "Chaos Fanatic",
                    "model" => "App\Boss\ChaosFanatic"
                ],
                [
                    "category_id" => 2,
                    "order" => 11,
                    "name" => "commander zilyana",
                    "alias" => "Commander Zilyana",
                    "model" => "App\Boss\CommanderZilyana"
                ],
                [
                    "category_id" => 2,
                    "order" => 12,
                    "name" => "corporeal beast",
                    "alias" => "Corporeal Beast",
                    "model" => "App\Boss\CorporealBeast"
                ],
                [
                    "category_id" => 2,
                    "order" => 13,
                    "name" => "crazy archaeologist",
                    "alias" => "Crazy Archaeologist",
                    "model" => "App\Boss\CrazyArchaeologist"
                ],
                [
                    "category_id" => 2,
                    "order" => 14,
                    "name" => "dagannoth kings",
                    "alias" => "Dagannoth Kings",
                    "model" => "App\Boss\DagannothKings"
                ],
                [
                    "category_id" => 2,
                    "order" => 15,
                    "name" => "dagannoth prime",
                    "alias" => "Dagannoth Prime",
                    "model" => "App\Boss\DagannothPrime"
                ],
                [
                    "category_id" => 2,
                    "order" => 16,
                    "name" => "dagannoth rex",
                    "alias" => "Dagannoth Rex",
                    "model" => "App\Boss\DagannothRex"
                ],
                [
                    "category_id" => 2,
                    "order" => 17,
                    "name" => "dagannoth supreme",
                    "alias" => "Dagannoth Surpeme",
                    "model" => "App\Boss\DagannothSupreme"
                ],
                [
                    "category_id" => 2,
                    "order" => 18,
                    "name" => "deranged archaeologist",
                    "alias" => "Deranged Archaeologist",
                    "model" => "App\Boss\DerangedArchaeologist"
                ],
                [
                    "category_id" => 2,
                    "order" => 19,
                    "name" => "general graardor",
                    "alias" => "General Graardor",
                    "model" => "App\Boss\GeneralGraardor"
                ],
                [
                    "category_id" => 2,
                    "order" => 20,
                    "name" => "giant mole",
                    "alias" => "Giant Mole",
                    "model" => "App\Boss\GiantMole"
                ],
                [
                    "category_id" => 2,
                    "order" => 21,
                    "name" => "grotesque guardians",
                    "alias" => "Grotesque Guardians",
                    "model" => "App\Boss\GrotesqueGuardians"
                ],
                [
                    "category_id" => 2,
                    "order" => 22,
                    "name" => "hespori",
                    "alias" => "Hespori",
                    "model" => "App\Boss\Hespori"
                ],
                [
                    "category_id" => 2,
                    "order" => 23,
                    "name" => "kalphite queen",
                    "alias" => "Kalphite Queen",
                    "model" => "App\Boss\KalphiteQueen"
                ],
                [
                    "category_id" => 2,
                    "order" => 24,
                    "name" => "king black dragon",
                    "alias" => "King Black Dragon",
                    "model" => "App\Boss\KingBlackDragon"
                ],
                [
                    "category_id" => 2,
                    "order" => 25,
                    "name" => "kraken",
                    "alias" => "Kraken",
                    "model" => "App\Boss\Kraken"
                ],
                [
                    "category_id" => 2,
                    "order" => 26,
                    "name" => "kreearra",
                    "alias" => "Kree'arra",
                    "model" => "App\Boss\KreeArra"
                ],
                [
                    "category_id" => 2,
                    "order" => 27,
                    "name" => "kril tsutsaroth",
                    "alias" => "K'ril Tsutsaroth",
                    "model" => "App\Boss\KrilTsutsaroth"
                ],
                [
                    "category_id" => 2,
                    "order" => 28,
                    "name" => "mimic",
                    "alias" => "Mimic",
                    "model" => "App\Boss\Mimic"
                ],
                [
                    "category_id" => 2,
                    "order" => 29,
                    "name" => "the nightmare",
                    "alias" => "Nightmare",
                    "model" => "App\Boss\TheNightmare"
                ],
                [
                    "category_id" => 2,
                    "order" => 30,
                    "name" => "the nightmare",
                    "alias" => "The Nightmare",
                    "model" => "App\Boss\TheNightmare"
                ],
                [
                    "category_id" => 2,
                    "order" => 31,
                    "name" => "obor",
                    "alias" => "Obor",
                    "model" => "App\Boss\Obor"
                ],
                [
                    "category_id" => 2,
                    "order" => 32,
                    "name" => "sarachnis",
                    "alias" => "Sarachnis",
                    "model" => "App\Boss\Sarachnis"
                ],
                [
                    "category_id" => 2,
                    "order" => 33,
                    "name" => "scorpia",
                    "alias" => "Scorpia",
                    "model" => "App\Boss\Scorpia"
                ],
                [
                    "category_id" => 2,
                    "order" => 34,
                    "name" => "skotizo",
                    "alias" => "Skotizo",
                    "model" => "App\Boss\Skotizo"
                ],
                [
                    "category_id" => 2,
                    "order" => 35,
                    "name" => "tempoross",
                    "alias" => "Tempoross",
                    "model" => "App\Boss\Tempoross"
                ],
                [
                    "category_id" => 2,
                    "order" => 36,
                    "name" => "the gauntlet",
                    "alias" => "The Gauntlet",
                    "model" => "App\Boss\TheGauntlet"
                ],
                [
                    "category_id" => 2,
                    "order" => 37,
                    "name" => "the corrupted gauntlet",
                    "alias" => "The Corrupted Gauntlet",
                    "model" => "App\Boss\TheCorruptedGauntlet"
                ],
                [
                    "category_id" => 3,
                    "order" => 38,
                    "name" => "theatre of blood",
                    "alias" => "Theatre of Blood",
                    "model" => "App\Raid\TheatreOfBlood"
                ],
                [
                    "category_id" => 2,
                    "order" => 39,
                    "name" => "thermonuclear smoke devil",
                    "alias" => "Thermonuclear Smoke Devil",
                    "model" => "App\Boss\ThermonuclearSmokeDevil"
                ],
                [
                    "category_id" => 2,
                    "order" => 40,
                    "name" => "the inferno",
                    "alias" => "The Inferno",
                    "model" => "App\Boss\TheInferno"
                ],
                [
                    "category_id" => 2,
                    "order" => 41,
                    "name" => "the inferno",
                    "alias" => "TzKal-Zuk",
                    "model" => "App\Boss\TheInferno"
                ],
                [
                    "category_id" => 2,
                    "order" => 42,
                    "name" => "the fight caves",
                    "alias" => "The Fight Caves",
                    "model" => "App\Boss\TheFightCaves"
                ],
                [
                    "category_id" => 2,
                    "order" => 43,
                    "name" => "the fight caves",
                    "alias" => "TzTok-Jad",
                    "model" => "App\Boss\TheFightCaves"
                ],
                [
                    "category_id" => 2,
                    "order" => 44,
                    "name" => "venenatis",
                    "alias" => "Venenatis",
                    "model" => "App\Boss\Venenatis"
                ],
                [
                    "category_id" => 2,
                    "order" => 45,
                    "name" => "vetion",
                    "alias" => "Vet'ion",
                    "model" => "App\Boss\Vetion"
                ],
                [
                    "category_id" => 2,
                    "order" => 46,
                    "name" => "vetion",
                    "alias" => "Vet'ion Reborn",
                    "model" => "App\Boss\Vetion"
                ],
                [
                    "category_id" => 2,
                    "order" => 47,
                    "name" => "vorkath",
                    "alias" => "Vorkath",
                    "model" => "App\Boss\Vorkath"
                ],
                [
                    "category_id" => 2,
                    "order" => 48,
                    "name" => "wintertodt",
                    "alias" => "Wintertodt",
                    "model" => "App\Boss\Wintertodt"
                ],
                [
                    "category_id" => 2,
                    "order" => 49,
                    "name" => "zalcano",
                    "alias" => "Zalcano",
                    "model" => "App\Boss\Zalcano"
                ],
                [
                    "category_id" => 2,
                    "order" => 50,
                    "name" => "zulrah",
                    "alias" => "Zulrah",
                    "model" => "App\Boss\Zulrah"
                ],

                [
                    "category_id" => 5,
                    "order" => 51,
                    "name" => "all treasure trails",
                    "alias" => "All Treasure Trails",
                    "model" => "App\Clues\AllTreasureTrails"
                ],
                [
                    "category_id" => 5,
                    "order" => 52,
                    "name" => "beginner treasure trails",
                    "alias" => "Clue scroll (beginner)",
                    "model" => "App\Clues\BeginnerTreasureTrails"
                ],
                [
                    "category_id" => 5,
                    "order" => 53,
                    "name" => "easy treasure trails",
                    "alias" => "Clue scroll (easy)",
                    "model" => "App\Clues\EasyTreasureTrails"
                ],
                [
                    "category_id" => 5,
                    "order" => 54,
                    "name" => "medium treasure trails",
                    "alias" => "Clue scroll (medium)",
                    "model" => "App\Clues\MediumTreasureTrails"
                ],
                [
                    "category_id" => 5,
                    "order" => 55,
                    "name" => "hard treasure trails",
                    "alias" => "Clue scroll (hard)",
                    "model" => "App\Clues\HardTreasureTrails"
                ],
                [
                    "category_id" => 5,
                    "order" => 56,
                    "name" => "elite treasure trails",
                    "alias" => "Clue scroll (elite)",
                    "model" => "App\Clues\EliteTreasureTrails"
                ],
                [
                    "category_id" => 5,
                    "order" => 57,
                    "name" => "master treasure trails",
                    "alias" => "Clue scroll (master)",
                    "model" => "App\Clues\MasterTreasureTrails"
                ],

                [
                    // TODO remove later
                    "category_id" => 4,
                    "order" => 58,
                    "name" => "goblin",
                    "alias" => "Goblin",
                    "model" => "App\Npc\Goblin"
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
        Schema::dropIfExists('collections');
    }
}
