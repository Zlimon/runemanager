<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
                    'order' => 1000,
                    'name' => 'Abyssal Sire',
                    'slug' => 'abyssal-sire',
                    'model' => 'App\Models\Boss\AbyssalSire'
                ],
                [
                    'category_id' => 2,
                    'order' => 1200,
                    'name' => 'Alchemical Hydra',
                    'slug' => 'alchemical-hydra',
                    'model' => 'App\Models\Boss\AlchemicalHydra'
                ],
                [
                    'category_id' => 2,
                    'order' => 1300,
                    'name' => 'Barrows Chests',
                    'slug' => 'barrows-chests',
                    'model' => 'App\Models\Boss\BarrowsChests'
                ],
                [
                    'category_id' => 2,
                    'order' => 1400,
                    'name' => 'Bryophyta',
                    'slug' => 'bryophyta',
                    'model' => 'App\Models\Boss\Bryophyta'
                ],
                [
                    'category_id' => 2,
                    'order' => 1500,
                    'name' => 'Callisto',
                    'slug' => 'callisto',
                    'model' => 'App\Models\Boss\Callisto'
                ],
                [
                    'category_id' => 2,
                    'order' => 1600,
                    'name' => 'Cerberus',
                    'slug' => 'cerberus',
                    'model' => 'App\Models\Boss\Cerberus'
                ],
                [
                    'category_id' => 3,
                    'order' => 1700,
                    'name' => 'Chambers of Xeric',
                    'slug' => 'chambers-of-xeric',
                    'model' => 'App\Models\Raid\ChambersOfXeric'
                ],
                [
                    'category_id' => 3,
                    'order' => 1800,
                    'name' => 'CoX: Challenge Mode',
                    'slug' => 'chambers-of-xeric-challenge-mode',
                    'model' => 'App\Models\Raid\ChambersOfXericChallengeMode'
                ],
                [
                    'category_id' => 2,
                    'order' => 1900,
                    'name' => 'Chaos Elemental',
                    'slug' => 'chaos-elemental',
                    'model' => 'App\Models\Boss\ChaosElemental'
                ],
                [
                    'category_id' => 2,
                    'order' => 2000,
                    'name' => 'Chaos Fanatic',
                    'slug' => 'chaos-fanatic',
                    'model' => 'App\Models\Boss\ChaosFanatic'
                ],
                [
                    'category_id' => 2,
                    'order' => 2100,
                    'name' => 'Commander Zilyana',
                    'slug' => 'commander-zilyana',
                    'model' => 'App\Models\Boss\CommanderZilyana'
                ],
                [
                    'category_id' => 2,
                    'order' => 2200,
                    'name' => 'Corporeal Beast',
                    'slug' => 'corporeal-beast',
                    'model' => 'App\Models\Boss\CorporealBeast'
                ],
                [
                    'category_id' => 2,
                    'order' => 2300,
                    'name' => 'Crazy Archaeologist',
                    'slug' => 'crazy-archaeologist',
                    'model' => 'App\Models\Boss\CrazyArchaeologist'
                ],
                [
                    'category_id' => 2,
                    'order' => 2400,
                    'name' => 'Dagannoth Kings',
                    'slug' => 'dagannoth-kings',
                    'model' => 'App\Models\Boss\DagannothKings'
                ],
                [
                    'category_id' => 2,
                    'order' => 2500,
                    'name' => 'Dagannoth Prime',
                    'slug' => 'dagannoth-prime',
                    'model' => 'App\Models\Boss\DagannothPrime'
                ],
                [
                    'category_id' => 2,
                    'order' => 2600,
                    'name' => 'Dagannoth Rex',
                    'slug' => 'dagannoth-rex',
                    'model' => 'App\Models\Boss\DagannothRex'
                ],
                [
                    'category_id' => 2,
                    'order' => 2700,
                    'name' => 'Dagannoth Supreme',
                    'slug' => 'dagannoth-supreme',
                    'model' => 'App\Models\Boss\DagannothSupreme'
                ],
                [
                    'category_id' => 2,
                    'order' => 2800,
                    'name' => 'Deranged Archaeologist',
                    'slug' => 'deranged-archaeologist',
                    'model' => 'App\Models\Boss\DerangedArchaeologist'
                ],
                [
                    'category_id' => 2,
                    'order' => 2900,
                    'name' => 'General Graardor',
                    'slug' => 'general-graardor',
                    'model' => 'App\Models\Boss\GeneralGraardor'
                ],
                [
                    'category_id' => 2,
                    'order' => 3000,
                    'name' => 'Giant Mole',
                    'slug' => 'giant-mole',
                    'model' => 'App\Models\Boss\GiantMole'
                ],
                [
                    'category_id' => 2,
                    'order' => 3100,
                    'name' => 'Grotesque Guardians',
                    'slug' => 'grotesque-guardians',
                    'model' => 'App\Models\Boss\GrotesqueGuardians'
                ],
                [
                    'category_id' => 2,
                    'order' => 3200,
                    'name' => 'Hespori',
                    'slug' => 'hespori',
                    'model' => 'App\Models\Boss\Hespori'
                ],
                [
                    'category_id' => 2,
                    'order' => 3300,
                    'name' => 'Kalphite Queen',
                    'slug' => 'kalphite-queen',
                    'model' => 'App\Models\Boss\KalphiteQueen'
                ],
                [
                    'category_id' => 2,
                    'order' => 3400,
                    'name' => 'King Black Dragon',
                    'slug' => 'king-black-dragon',
                    'model' => 'App\Models\Boss\KingBlackDragon'
                ],
                [
                    'category_id' => 2,
                    'order' => 3500,
                    'name' => 'Kraken',
                    'slug' => 'kraken',
                    'model' => 'App\Models\Boss\Kraken'
                ],
                [
                    'category_id' => 2,
                    'order' => 3600,
                    'name' => 'Kree\'arra',
                    'slug' => 'kreearra',
                    'model' => 'App\Models\Boss\KreeArra'
                ],
                [
                    'category_id' => 2,
                    'order' => 3700,
                    'name' => 'K\'ril Tsutsaroth',
                    'slug' => 'kril-tsutsaroth',
                    'model' => 'App\Models\Boss\KrilTsutsaroth'
                ],
                [
                    'category_id' => 2,
                    'order' => 3800,
                    'name' => 'Mimic',
                    'slug' => 'mimic',
                    'model' => 'App\Models\Boss\Mimic'
                ],
                [
                    'category_id' => 2,
                    'order' => 3900,
                    'name' => 'Nightmare',
                    'slug' => 'nightmare',
                    'model' => 'App\Models\Boss\Nightmare'
                ],
                [
                    'category_id' => 2,
                    'order' => 4000,
                    'name' => 'The Nightmare',
                    'slug' => 'nightmare',
                    'model' => 'App\Models\Boss\Nightmare'
                ],
                [
                    'category_id' => 2,
                    'order' => 4100,
                    'name' => 'Phosani\'s Nightmare',
                    'slug' => 'phosanis-nightmare',
                    'model' => 'App\Models\Boss\PhosanisNightmare'
                ],
                [
                    'category_id' => 2,
                    'order' => 4200,
                    'name' => 'Obor',
                    'slug' => 'obor',
                    'model' => 'App\Models\Boss\Obor'
                ],
                [
                    'category_id' => 2,
                    'order' => 4300,
                    'name' => 'Sarachnis',
                    'slug' => 'sarachnis',
                    'model' => 'App\Models\Boss\Sarachnis'
                ],
                [
                    'category_id' => 2,
                    'order' => 4400,
                    'name' => 'Scorpia',
                    'slug' => 'scorpia',
                    'model' => 'App\Models\Boss\Scorpia'
                ],
                [
                    'category_id' => 2,
                    'order' => 4500,
                    'name' => 'Skotizo',
                    'slug' => 'skotizo',
                    'model' => 'App\Models\Boss\Skotizo'
                ],
                [
                    'category_id' => 2,
                    'order' => 4600,
                    'name' => 'Tempoross',
                    'slug' => 'tempoross',
                    'model' => 'App\Models\Boss\Tempoross'
                ],
                [
                    'category_id' => 2,
                    'order' => 4700,
                    'name' => 'The Gauntlet',
                    'slug' => 'the-gauntlet',
                    'model' => 'App\Models\Boss\TheGauntlet'
                ],
                [
                    'category_id' => 2,
                    'order' => 4800,
                    'name' => 'The Corrupted Gauntlet',
                    'slug' => 'the-corrupted-gauntlet',
                    'model' => 'App\Models\Boss\TheCorruptedGauntlet'
                ],
                [
                    'category_id' => 3,
                    'order' => 4900,
                    'name' => 'Theatre of Blood',
                    'slug' => 'theatre-of-blood',
                    'model' => 'App\Models\Raid\TheatreOfBlood'
                ],
                [
                    'category_id' => 3,
                    'order' => 5000,
                    'name' => 'ToB: Hard Mode',
                    'slug' => 'theatre-of-blood-hard-mode',
                    'model' => 'App\Models\Raid\TheatreOfBloodHardMode'
                ],
                [
                    'category_id' => 2,
                    'order' => 5100,
                    'name' => 'Thermonuclear Smoke Devil',
                    'slug' => 'thermonuclear-smoke-devil',
                    'model' => 'App\Models\Boss\ThermonuclearSmokeDevil'
                ],
                [
                    'category_id' => 2,
                    'order' => 5200,
                    'name' => 'TzKal-Zuk',
                    'slug' => 'tzkal-zuk',
                    'model' => 'App\Models\Boss\TzKalZuk'
                ],
                [
                    'category_id' => 2,
                    'order' => 5300,
                    'name' => 'The Inferno',
                    'slug' => 'tzkal-zuk',
                    'model' => 'App\Models\Boss\TzKalZuk'
                ],
                [
                    'category_id' => 2,
                    'order' => 5400,
                    'name' => 'TzTok-Jad',
                    'slug' => 'tztok-jad',
                    'model' => 'App\Models\Boss\TzTokJad'
                ],
                [
                    'category_id' => 2,
                    'order' => 5500,
                    'name' => 'The Fight Caves',
                    'slug' => 'tztok-jad',
                    'model' => 'App\Models\Boss\TzTokJad'
                ],
                [
                    'category_id' => 2,
                    'order' => 5600,
                    'name' => 'Venenatis',
                    'slug' => 'venenatis',
                    'model' => 'App\Models\Boss\Venenatis'
                ],
                [
                    'category_id' => 2,
                    'order' => 5700,
                    'name' => 'Vet\'ion',
                    'slug' => 'vetion',
                    'model' => 'App\Models\Boss\VetIon'
                ],
                [
                    'category_id' => 2,
                    'order' => 5800,
                    'name' => 'Vet\'ion Reborn',
                    'slug' => 'vetion',
                    'model' => 'App\Models\Boss\Vetion'
                ],
                [
                    'category_id' => 2,
                    'order' => 5900,
                    'name' => 'Vorkath',
                    'slug' => 'vorkath',
                    'model' => 'App\Models\Boss\Vorkath'
                ],
                [
                    'category_id' => 2,
                    'order' => 6000,
                    'name' => 'Wintertodt',
                    'slug' => 'wintertodt',
                    'model' => 'App\Models\Boss\Wintertodt'
                ],
                [
                    'category_id' => 2,
                    'order' => 6100,
                    'name' => 'Zalcano',
                    'slug' => 'zalcano',
                    'model' => 'App\Models\Boss\Zalcano'
                ],
                [
                    'category_id' => 2,
                    'order' => 6200,
                    'name' => 'Zulrah',
                    'slug' => 'zulrah',
                    'model' => 'App\Models\Boss\Zulrah'
                ],

                [
                    'category_id' => 5,
                    'order' => 6300,
                    'name' => 'All Treasure Trails',
                    'slug' => 'all-treasure-trails',
                    'model' => 'App\Models\Clue\AllTreasureTrails'
                ],
                [
                    'category_id' => 5,
                    'order' => 6400,
                    'name' => 'Clue scroll (beginner)',
                    'slug' => 'beginner-treasure-trails',
                    'model' => 'App\Models\Clue\BeginnerTreasureTrails'
                ],
                [
                    'category_id' => 5,
                    'order' => 6500,
                    'name' => 'Clue scroll (easy)',
                    'slug' => 'easy-treasure-trails',
                    'model' => 'App\Models\Clue\EasyTreasureTrails'
                ],
                [
                    'category_id' => 5,
                    'order' => 6600,
                    'name' => 'Clue scroll (medium)',
                    'slug' => 'medium-treasure-trails',
                    'model' => 'App\Models\Clue\MediumTreasureTrails'
                ],
                [
                    'category_id' => 5,
                    'order' => 6700,
                    'name' => 'Clue scroll (hard)',
                    'slug' => 'hard-treasure-trails',
                    'model' => 'App\Models\Clue\HardTreasureTrails'
                ],
                [
                    'category_id' => 5,
                    'order' => 6800,
                    'name' => 'Clue scroll (elite)',
                    'slug' => 'elite-treasure-trails',
                    'model' => 'App\Models\Clue\EliteTreasureTrails'
                ],
                [
                    'category_id' => 5,
                    'order' => 6900,
                    'name' => 'Clue scroll (master)',
                    'slug' => 'master-treasure-trails',
                    'model' => 'App\Models\Clue\MasterTreasureTrails'
                ],

                [
                    // TODO remove later
                    'category_id' => 4,
                    'order' => 7000,
                    'name' => 'Goblin',
                    'slug' => 'goblin',
                    'model' => 'App\Models\Npc\Goblin'
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
