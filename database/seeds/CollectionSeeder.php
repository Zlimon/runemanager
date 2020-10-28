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
			["name" => "abyssal sire", "type" => "boss", "model" => "App\Boss\AbyssalSire"],
			["name" => "alchemical hydra", "type" => "boss", "model" => "App\Boss\AlchemicalHydra"],
			["name" => "barrows chests", "type" => "boss", "model" => "App\Boss\BarrowsChests"],
			["name" => "bryophyta", "type" => "boss", "model" => "App\Boss\Bryophyta"],
			["name" => "callisto", "type" => "boss", "model" => "App\Boss\Callisto"],
			["name" => "cerberus", "type" => "boss", "model" => "App\Boss\Cerberus"],
			["name" => "chambers of xeric", "type" => "raid", "model" => "App\Raid\ChambersOfXeric"],
			["name" => "chambers of xeric challenge mode", "type" => "raid", "model" => "App\Raid\ChambersOfXericChallengeMode"],
			["name" => "chaos elemental", "type" => "boss", "model" => "App\Boss\ChaosElemental"],
			["name" => "chaos fanatic", "type" => "boss", "model" => "App\Boss\ChaosFanatic"],
			["name" => "commander zilyana", "type" => "boss", "model" => "App\Boss\CommanderZilyana"],
			["name" => "corporeal beast", "type" => "boss", "model" => "App\Boss\CorporealBeast"],
			["name" => "crazy archaeologist", "type" => "boss", "model" => "App\Boss\CrazyArchaeologist"],
			["name" => "dagannoth prime", "type" => "boss", "model" => "App\Boss\DagannothPrime"],
			["name" => "dagannoth rex", "type" => "boss", "model" => "App\Boss\DagannothRex"],
			["name" => "dagannoth supreme", "type" => "boss", "model" => "App\Boss\DagannothSupreme"],
			["name" => "deranged archaeologist", "type" => "boss", "model" => "App\Boss\DerangedArchaeologist"],
			["name" => "general graardor", "type" => "boss", "model" => "App\Boss\GeneralGraardor"],
			["name" => "giant mole", "type" => "boss", "model" => "App\Boss\GiantMole"],
			["name" => "grotesque guardians", "type" => "boss", "model" => "App\Boss\GrotesqueGuardians"],
			["name" => "hespori", "type" => "boss", "model" => "App\Boss\Hespori"],
			["name" => "kalphite queen", "type" => "boss", "model" => "App\Boss\KalphiteQueen"],
			["name" => "king black dragon", "type" => "boss", "model" => "App\Boss\KingBlackDragon"],
			["name" => "kraken", "type" => "boss", "model" => "App\Boss\Kraken"],
			["name" => "kreearra", "type" => "boss", "model" => "App\Boss\KreeArra"],
			["name" => "kril tsutsaroth", "type" => "boss", "model" => "App\Boss\KrilTsutsaroth"],
			["name" => "mimic", "type" => "boss", "model" => "App\Boss\Mimic"],
			["name" => "nightmare", "type" => "boss", "model" => "App\Boss\TheNightmare"],
			["name" => "obor", "type" => "boss", "model" => "App\Boss\Obor"],
			["name" => "sarachnis", "type" => "boss", "model" => "App\Boss\Sarachnis"],
			["name" => "scorpia", "type" => "boss", "model" => "App\Boss\Scorpia"],
			["name" => "skotizo", "type" => "boss", "model" => "App\Boss\Skotizo"],
			["name" => "the gauntlet", "type" => "boss", "model" => "App\Boss\TheGauntlet"],
			["name" => "the corrupted gauntlet", "type" => "boss", "model" => "App\Boss\TheCorruptedGauntlet"],
			["name" => "theatre of blood", "type" => "raid", "model" => "App\Raid\TheatreOfBlood"],
			["name" => "thermonuclear smoke devil", "type" => "boss", "model" => "App\Boss\ThermonuclearSmokeDevil"],
			["name" => "tzkal zuk", "type" => "boss", "model" => "App\Boss\TheInferno"],
			["name" => "tztok jad", "type" => "boss", "model" => "App\Boss\TheFightCaves"],
			["name" => "venenatis", "type" => "boss", "model" => "App\Boss\Venenatis"],
			["name" => "vetion", "type" => "boss", "model" => "App\Boss\Vetion"],
			["name" => "vorkath", "type" => "boss", "model" => "App\Boss\Vorkath"],
			["name" => "wintertodt", "type" => "boss", "model" => "App\Boss\Wintertodt"],
			["name" => "zalcano", "type" => "boss", "model" => "App\Boss\Zalcano"],
			["name" => "zulrah", "type" => "boss", "model" => "App\Boss\Zulrah"],

			["name" => "dagannoth kings", "type" => "boss", "model" => "App\Boss\DagannothKings"],
			["name" => "the fight caves", "type" => "boss", "model" => "App\Boss\TheFightCaves"],
			["name" => "the inferno", "type" => "boss", "model" => "App\Boss\TheInferno"],
			["name" => "the nightmare", "type" => "boss", "model" => "App\Boss\TheNightmare"],

			["name" => "goblin", "type" => "boss", "model" => "App\Boss\Goblin"], // TODO remove later
		]);
    }
}
