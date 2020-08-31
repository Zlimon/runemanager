<?php

use Illuminate\Database\Seeder;

class CollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		DB::table('collections')->insert([
			["name" => "abyssal sire", "collection_type" => "App\Boss\AbyssalSire"],
			["name" => "alchemical hydra", "collection_type" => "App\Boss\AlchemicalHydra"],
			["name" => "barrows chests", "collection_type" => "App\Boss\BarrowsChests"],
			["name" => "bryophyta", "collection_type" => "App\Boss\Bryophyta"],
			["name" => "callisto", "collection_type" => "App\Boss\Callisto"],
			["name" => "cerberus", "collection_type" => "App\Boss\Cerberus"],
			["name" => "chambers of xeric", "collection_type" => "App\Raid\ChambersOfXeric"],
			["name" => "chambers of xeric challenge mode", "collection_type" => "App\Raid\ChambersOfXericChallengeMode"],
			["name" => "chaos elemental", "collection_type" => "App\Boss\ChaosElemental"],
			["name" => "chaos fanatic", "collection_type" => "App\Boss\ChaosFanatic"],
			["name" => "commander zilyana", "collection_type" => "App\Boss\CommanderZilyana"],
			["name" => "corporeal beast", "collection_type" => "App\Boss\CorporealBeast"],
			["name" => "crazy archaeologist", "collection_type" => "App\Boss\CrazyArchaeologist"],
			["name" => "dagannoth prime", "collection_type" => "App\Boss\DagannothPrime"],
			["name" => "dagannoth rex", "collection_type" => "App\Boss\DagannothRex"],
			["name" => "dagannoth supreme", "collection_type" => "App\Boss\DagannothSupreme"],
			["name" => "deranged archaeologist", "collection_type" => "App\Boss\DerangedArchaeologist"],
			["name" => "general graardor", "collection_type" => "App\Boss\GeneralGraardor"],
			["name" => "giant mole", "collection_type" => "App\Boss\GiantMole"],
			["name" => "grotesque guardians", "collection_type" => "App\Boss\GrotesqueGuardians"],
			["name" => "hespori", "collection_type" => "App\Boss\Hespori"],
			["name" => "kalphite queen", "collection_type" => "App\Boss\KalphiteQueen"],
			["name" => "king black dragon", "collection_type" => "App\Boss\KingBlackDragon"],
			["name" => "kraken", "collection_type" => "App\Boss\Kraken"],
			["name" => "kreearra", "collection_type" => "App\Boss\KreeArra"],
			["name" => "kril tsutsaroth", "collection_type" => "App\Boss\KrilTsutsaroth"],
			["name" => "mimic", "collection_type" => "App\Boss\Mimic"],
			["name" => "nightmare", "collection_type" => "App\Boss\TheNightmare"],
			["name" => "obor", "collection_type" => "App\Boss\Obor"],
			["name" => "sarachnis", "collection_type" => "App\Boss\Sarachnis"],
			["name" => "scorpia", "collection_type" => "App\Boss\Scorpia"],
			["name" => "skotizo", "collection_type" => "App\Boss\Skotizo"],
			["name" => "the gauntlet", "collection_type" => "App\Boss\TheGauntlet"],
			["name" => "the corrupted gauntlet", "collection_type" => "App\Boss\TheCorruptedGauntlet"],
			["name" => "theatre of blood", "collection_type" => "App\Raid\TheatreOfBlood"],
			["name" => "thermonuclear smoke devil", "collection_type" => "App\Boss\ThermonuclearSmokeDevil"],
			["name" => "tzkal zuk", "collection_type" => "App\Boss\TheInferno"],
			["name" => "tztok jad", "collection_type" => "App\Boss\TheFightCaves"],
			["name" => "venenatis", "collection_type" => "App\Boss\Venenatis"],
			["name" => "vetion", "collection_type" => "App\Boss\Vetion"],
			["name" => "vorkath", "collection_type" => "App\Boss\Vorkath"],
			["name" => "wintertodt", "collection_type" => "App\Boss\Wintertodt"],
			["name" => "zalcano", "collection_type" => "App\Boss\Zalcano"],
			["name" => "zulrah", "collection_type" => "App\Boss\Zulrah"],

			["name" => "dagannoth kings", "collection_type" => "App\Boss\DagannothKings"],
			["name" => "the fight caves", "collection_type" => "App\Boss\TheFightCaves"],
			["name" => "the inferno", "collection_type" => "App\Boss\TheInferno"],
			["name" => "the nightmare", "collection_type" => "App\Boss\TheNightmare"],

			["name" => "goblin", "collection_type" => "App\Boss\Goblin"], // TODO remove later
		]);
    }
}
