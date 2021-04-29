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
            $table->foreignId('category_id')->constrained();
            $table->integer('order')->unsigned()->unique();
            $table->string('name')->unique();
            $table->string('slug');
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
                    'category_id' => 2,
                    'order' => 1,
                    'name' => 'Abyssal Sire',
                    'slug' => 'abyssal-sire',
                    'model' => 'App\Boss\AbyssalSire'
                ],
                [
                    'category_id' => 2,
                    'order' => 2,
                    'name' => 'Alchemical Hydra',
                    'slug' => 'alchemical-hydra',
                    'model' => 'App\Boss\AlchemicalHydra'
                ],
                [
                    'category_id' => 2,
                    'order' => 3,
                    'name' => 'Barrows Chests',
                    'slug' => 'barrows-chests',
                    'model' => 'App\Boss\BarrowsChests'
                ],
                [
                    'category_id' => 2,
                    'order' => 4,
                    'name' => 'Bryophyta',
                    'slug' => 'bryophyta',
                    'model' => 'App\Boss\Bryophyta'
                ],
                [
                    'category_id' => 2,
                    'order' => 5,
                    'name' => 'Callisto',
                    'slug' => 'callisto',
                    'model' => 'App\Boss\Callisto'
                ],
                [
                    'category_id' => 2,
                    'order' => 6,
                    'name' => 'Cerberus',
                    'slug' => 'cerberus',
                    'model' => 'App\Boss\Cerberus'
                ],
                [
                    'category_id' => 3,
                    'order' => 7,
                    'name' => 'Chambers of Xeric',
                    'slug' => 'chambers-of-xeric',
                    'model' => 'App\Raid\ChambersOfXeric'
                ],
                [
                    'category_id' => 3,
                    'order' => 8,
                    'name' => 'CoX: Challenge Mode',
                    'slug' => 'chambers-of-xeric-challenge-mode',
                    'model' => 'App\Raid\ChambersOfXericChallengeMode'
                ],
                [
                    'category_id' => 2,
                    'order' => 9,
                    'name' => 'Chaos Elemental',
                    'slug' => 'chaos-elemental',
                    'model' => 'App\Boss\ChaosElemental'
                ],
                [
                    'category_id' => 2,
                    'order' => 10,
                    'name' => 'Chaos Fanatic',
                    'slug' => 'chaos-fanatic',
                    'model' => 'App\Boss\ChaosFanatic'
                ],
                [
                    'category_id' => 2,
                    'order' => 11,
                    'name' => 'Commander Zilyana',
                    'slug' => 'commander-zilyana',
                    'model' => 'App\Boss\CommanderZilyana'
                ],
                [
                    'category_id' => 2,
                    'order' => 12,
                    'name' => 'Corporeal Beast',
                    'slug' => 'corporeal-beast',
                    'model' => 'App\Boss\CorporealBeast'
                ],
                [
                    'category_id' => 2,
                    'order' => 13,
                    'name' => 'Crazy Archaeologist',
                    'slug' => 'crazy-archaeologist',
                    'model' => 'App\Boss\CrazyArchaeologist'
                ],
                [
                    'category_id' => 2,
                    'order' => 14,
                    'name' => 'Dagannoth Kings',
                    'slug' => 'dagannoth-kings',
                    'model' => 'App\Boss\DagannothKings'
                ],
                [
                    'category_id' => 2,
                    'order' => 15,
                    'name' => 'Dagannoth Prime',
                    'slug' => 'dagannoth-rime',
                    'model' => 'App\Boss\DagannothPrime'
                ],
                [
                    'category_id' => 2,
                    'order' => 16,
                    'name' => 'Dagannoth Rex',
                    'slug' => 'dagannoth-ex',
                    'model' => 'App\Boss\DagannothRex'
                ],
                [
                    'category_id' => 2,
                    'order' => 17,
                    'name' => 'Dagannoth Surpeme',
                    'slug' => 'dagannoth-supreme',
                    'model' => 'App\Boss\DagannothSupreme'
                ],
                [
                    'category_id' => 2,
                    'order' => 18,
                    'name' => 'Deranged Archaeologist',
                    'slug' => 'deranged-archaeologist',
                    'model' => 'App\Boss\DerangedArchaeologist'
                ],
                [
                    'category_id' => 2,
                    'order' => 19,
                    'name' => 'General Graardor',
                    'slug' => 'general-graardor',
                    'model' => 'App\Boss\GeneralGraardor'
                ],
                [
                    'category_id' => 2,
                    'order' => 20,
                    'name' => 'Giant Mole',
                    'slug' => 'giant-mole',
                    'model' => 'App\Boss\GiantMole'
                ],
                [
                    'category_id' => 2,
                    'order' => 21,
                    'name' => 'Grotesque Guardians',
                    'slug' => 'grotesque-guardians',
                    'model' => 'App\Boss\GrotesqueGuardians'
                ],
                [
                    'category_id' => 2,
                    'order' => 22,
                    'name' => 'Hespori',
                    'slug' => 'hespori',
                    'model' => 'App\Boss\Hespori'
                ],
                [
                    'category_id' => 2,
                    'order' => 23,
                    'name' => 'Kalphite Queen',
                    'slug' => 'kalphite-queen',
                    'model' => 'App\Boss\KalphiteQueen'
                ],
                [
                    'category_id' => 2,
                    'order' => 24,
                    'name' => 'King Black Dragon',
                    'slug' => 'king-black-dragon',
                    'model' => 'App\Boss\KingBlackDragon'
                ],
                [
                    'category_id' => 2,
                    'order' => 25,
                    'name' => 'Kraken',
                    'slug' => 'kraken',
                    'model' => 'App\Boss\Kraken'
                ],
                [
                    'category_id' => 2,
                    'order' => 26,
                    'name' => 'Kree"arra',
                    'slug' => 'kreearra',
                    'model' => 'App\Boss\KreeArra'
                ],
                [
                    'category_id' => 2,
                    'order' => 27,
                    'name' => 'K"ril Tsutsaroth',
                    'slug' => 'kril-tsutsaroth',
                    'model' => 'App\Boss\KrilTsutsaroth'
                ],
                [
                    'category_id' => 2,
                    'order' => 28,
                    'name' => 'Mimic',
                    'slug' => 'mimic',
                    'model' => 'App\Boss\Mimic'
                ],
                [
                    'category_id' => 2,
                    'order' => 29,
                    'name' => 'Nightmare',
                    'slug' => 'nightmare',
                    'model' => 'App\Boss\Nightmare'
                ],
                [
                    'category_id' => 2,
                    'order' => 30,
                    'name' => 'The Nightmare',
                    'slug' => 'nightmare',
                    'model' => 'App\Boss\Nightmare'
                ],
                [
                    'category_id' => 2,
                    'order' => 31,
                    'name' => 'Obor',
                    'slug' => 'obor',
                    'model' => 'App\Boss\Obor'
                ],
                [
                    'category_id' => 2,
                    'order' => 32,
                    'name' => 'Sarachnis',
                    'slug' => 'sarachnis',
                    'model' => 'App\Boss\Sarachnis'
                ],
                [
                    'category_id' => 2,
                    'order' => 33,
                    'name' => 'Scorpia',
                    'slug' => 'scorpia',
                    'model' => 'App\Boss\Scorpia'
                ],
                [
                    'category_id' => 2,
                    'order' => 34,
                    'name' => 'Skotizo',
                    'slug' => 'skotizo',
                    'model' => 'App\Boss\Skotizo'
                ],
                [
                    'category_id' => 2,
                    'order' => 35,
                    'name' => 'Tempoross',
                    'slug' => 'tempoross',
                    'model' => 'App\Boss\Tempoross'
                ],
                [
                    'category_id' => 2,
                    'order' => 36,
                    'name' => 'The Gauntlet',
                    'slug' => 'the-gauntlet',
                    'model' => 'App\Boss\TheGauntlet'
                ],
                [
                    'category_id' => 2,
                    'order' => 37,
                    'name' => 'The Corrupted Gauntlet',
                    'slug' => 'the-corrupted-gauntlet',
                    'model' => 'App\Boss\TheCorruptedGauntlet'
                ],
                [
                    'category_id' => 3,
                    'order' => 38,
                    'name' => 'Theatre of Blood',
                    'slug' => 'theatre-of-blood',
                    'model' => 'App\Raid\TheatreOfBlood'
                ],
                [
                    'category_id' => 2,
                    'order' => 39,
                    'name' => 'Thermonuclear Smoke Devil',
                    'slug' => 'thermonuclear-smoke-devil',
                    'model' => 'App\Boss\ThermonuclearSmokeDevil'
                ],
                [
                    'category_id' => 2,
                    'order' => 40,
                    'name' => 'TzKal-Zuk',
                    'slug' => 'tzkal-zuk',
                    'model' => 'App\Boss\TzKalZuk'
                ],
                [
                    'category_id' => 2,
                    'order' => 41,
                    'name' => 'The Inferno',
                    'slug' => 'tzkal-zuk',
                    'model' => 'App\Boss\TzKalZuk'
                ],
                [
                    'category_id' => 2,
                    'order' => 42,
                    'name' => 'TzTok-Jad',
                    'slug' => 'tztok-jad',
                    'model' => 'App\Boss\TzTokJad'
                ],
                [
                    'category_id' => 2,
                    'order' => 43,
                    'name' => 'The Fight Caves',
                    'slug' => 'tztok-jad',
                    'model' => 'App\Boss\TzTokJad'
                ],
                [
                    'category_id' => 2,
                    'order' => 44,
                    'name' => 'Venenatis',
                    'slug' => 'venenatis',
                    'model' => 'App\Boss\Venenatis'
                ],
                [
                    'category_id' => 2,
                    'order' => 45,
                    'name' => 'Vet"ion',
                    'slug' => 'vetion',
                    'model' => 'App\Boss\VetIon'
                ],
                [
                    'category_id' => 2,
                    'order' => 46,
                    'name' => 'Vet"ion Reborn',
                    'slug' => 'vetion',
                    'model' => 'App\Boss\Vetion'
                ],
                [
                    'category_id' => 2,
                    'order' => 47,
                    'name' => 'Vorkath',
                    'slug' => 'vorkath',
                    'model' => 'App\Boss\Vorkath'
                ],
                [
                    'category_id' => 2,
                    'order' => 48,
                    'name' => 'Wintertodt',
                    'slug' => 'wintertodt',
                    'model' => 'App\Boss\Wintertodt'
                ],
                [
                    'category_id' => 2,
                    'order' => 49,
                    'name' => 'Zalcano',
                    'slug' => 'zalcano',
                    'model' => 'App\Boss\Zalcano'
                ],
                [
                    'category_id' => 2,
                    'order' => 50,
                    'name' => 'Zulrah',
                    'slug' => 'zulrah',
                    'model' => 'App\Boss\Zulrah'
                ],

                [
                    'category_id' => 5,
                    'order' => 51,
                    'name' => 'All Treasure Trails',
                    'slug' => 'all-treasure-trails',
                    'model' => 'App\Clues\AllTreasureTrails'
                ],
                [
                    'category_id' => 5,
                    'order' => 52,
                    'name' => 'Clue scroll (beginner)',
                    'slug' => 'beginner-treasure-trails',
                    'model' => 'App\Clues\BeginnerTreasureTrails'
                ],
                [
                    'category_id' => 5,
                    'order' => 53,
                    'name' => 'Clue scroll (easy)',
                    'slug' => 'easy-treasure-trails',
                    'model' => 'App\Clues\EasyTreasureTrails'
                ],
                [
                    'category_id' => 5,
                    'order' => 54,
                    'name' => 'Clue scroll (medium)',
                    'slug' => 'medium-treasure-trails',
                    'model' => 'App\Clues\MediumTreasureTrails'
                ],
                [
                    'category_id' => 5,
                    'order' => 55,
                    'name' => 'Clue scroll (hard)',
                    'slug' => 'hard-treasure-trails',
                    'model' => 'App\Clues\HardTreasureTrails'
                ],
                [
                    'category_id' => 5,
                    'order' => 56,
                    'name' => 'Clue scroll (elite)',
                    'slug' => 'elite-treasure-trails',
                    'model' => 'App\Clues\EliteTreasureTrails'
                ],
                [
                    'category_id' => 5,
                    'order' => 57,
                    'name' => 'Clue scroll (master)',
                    'slug' => 'master-treasure-trails',
                    'model' => 'App\Clues\MasterTreasureTrails'
                ],

                [
                    // TODO remove later
                    'category_id' => 4,
                    'order' => 58,
                    'name' => 'Goblin',
                    'slug' => 'goblin',
                    'model' => 'App\Npc\Goblin'
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
