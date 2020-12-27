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
			["name" => "abyssal sire", "alias" => "Abyssal Sire", "type" => "boss", "model" => "App\Boss\AbyssalSire"],
			["name" => "alchemical hydra", "alias" => "Alchemical Hydra", "type" => "boss", "model" => "App\Boss\AlchemicalHydra"],
			["name" => "barrows chests", "alias" => "Barrows Chests", "type" => "boss", "model" => "App\Boss\BarrowsChests"],
			["name" => "bryophyta", "alias" => "Bryophyta", "type" => "boss", "model" => "App\Boss\Bryophyta"],
			["name" => "callisto", "alias" => "Callisto", "type" => "boss", "model" => "App\Boss\Callisto"],
			["name" => "cerberus", "alias" => "Cerberus", "type" => "boss", "model" => "App\Boss\Cerberus"],
			["name" => "chambers of xeric", "alias" => "Chambers of Xeric", "type" => "raid", "model" => "App\Raid\ChambersOfXeric"],
			["name" => "chambers of xeric challenge mode", "alias" => "COX: Challenge Mode", "type" => "raid", "model" => "App\Raid\ChambersOfXericChallengeMode"],
			["name" => "chaos elemental", "alias" => "Chaos Elemental", "type" => "boss", "model" => "App\Boss\ChaosElemental"],
			["name" => "chaos fanatic", "alias" => "Chaos Fanatic", "type" => "boss", "model" => "App\Boss\ChaosFanatic"],
			["name" => "commander zilyana", "alias" => "Commander Zilyana", "type" => "boss", "model" => "App\Boss\CommanderZilyana"],
			["name" => "corporeal beast", "alias" => "Corporeal Beast", "type" => "boss", "model" => "App\Boss\CorporealBeast"],
			["name" => "crazy archaeologist", "alias" => "Crazy Archaeologist", "type" => "boss", "model" => "App\Boss\CrazyArchaeologist"],
			["name" => "dagannoth prime", "alias" => "Dagannoth Prime", "type" => "boss", "model" => "App\Boss\DagannothPrime"],
			["name" => "dagannoth rex", "alias" => "Dagannoth Rex", "type" => "boss", "model" => "App\Boss\DagannothRex"],
			["name" => "dagannoth supreme", "alias" => "Dagannoth Surpeme", "type" => "boss", "model" => "App\Boss\DagannothSupreme"],
			["name" => "deranged archaeologist", "alias" => "Deranged Archaeologist", "type" => "boss", "model" => "App\Boss\DerangedArchaeologist"],
			["name" => "general graardor", "alias" => "General Graardor", "type" => "boss", "model" => "App\Boss\GeneralGraardor"],
			["name" => "giant mole", "alias" => "Giant Mole", "type" => "boss", "model" => "App\Boss\GiantMole"],
			["name" => "grotesque guardians", "alias" => "Grotesque Guardians", "type" => "boss", "model" => "App\Boss\GrotesqueGuardians"],
			["name" => "hespori", "alias" => "Hespori", "type" => "boss", "model" => "App\Boss\Hespori"],
			["name" => "kalphite queen", "alias" => "Kalphite Queen", "type" => "boss", "model" => "App\Boss\KalphiteQueen"],
			["name" => "king black dragon", "alias" => "King Black Dragon", "type" => "boss", "model" => "App\Boss\KingBlackDragon"],
			["name" => "kraken", "alias" => "Kraken", "type" => "boss", "model" => "App\Boss\Kraken"],
			["name" => "kreearra", "alias" => "Kree'arra", "type" => "boss", "model" => "App\Boss\KreeArra"],
			["name" => "kril tsutsaroth", "alias" => "K'ril Tsutsaroth", "type" => "boss", "model" => "App\Boss\KrilTsutsaroth"],
			["name" => "mimic", "alias" => "Mimic", "type" => "boss", "model" => "App\Boss\Mimic"],
			// ["name" => "nightmare", "alias" => "Nightmare", "type" => "boss", "model" => "App\Boss\TheNightmare"],
			["name" => "obor", "alias" => "Obor", "type" => "boss", "model" => "App\Boss\Obor"],
			["name" => "sarachnis", "alias" => "Sarachnis", "type" => "boss", "model" => "App\Boss\Sarachnis"],
			["name" => "scorpia", "alias" => "Scorpia", "type" => "boss", "model" => "App\Boss\Scorpia"],
			["name" => "skotizo", "alias" => "Skotizo", "type" => "boss", "model" => "App\Boss\Skotizo"],
			["name" => "the gauntlet", "alias" => "The Gauntlet", "type" => "boss", "model" => "App\Boss\TheGauntlet"],
			["name" => "the corrupted gauntlet", "alias" => "The Corrupted Gauntlet", "type" => "boss", "model" => "App\Boss\TheCorruptedGauntlet"],
			["name" => "theatre of blood", "alias" => "Theatre of Blood", "type" => "raid", "model" => "App\Raid\TheatreOfBlood"],
			["name" => "thermonuclear smoke devil", "alias" => "Thermonuclear Smoke Devil", "type" => "boss", "model" => "App\Boss\ThermonuclearSmokeDevil"],
			["name" => "tzkal zuk", "alias" => "TzKal-Zuk", "type" => "boss", "model" => "App\Boss\TheInferno"],
			["name" => "tztok jad", "alias" => "TzTok-Jad", "type" => "boss", "model" => "App\Boss\TheFightCaves"],
			["name" => "venenatis", "alias" => "Venenatis", "type" => "boss", "model" => "App\Boss\Venenatis"],
			["name" => "vetion", "alias" => "Vetion", "type" => "boss", "model" => "App\Boss\Vetion"],
			["name" => "vorkath", "alias" => "Vorkath", "type" => "boss", "model" => "App\Boss\Vorkath"],
			["name" => "wintertodt", "alias" => "Wintertodt", "type" => "boss", "model" => "App\Boss\Wintertodt"],
			["name" => "zalcano", "alias" => "Zalcano", "type" => "boss", "model" => "App\Boss\Zalcano"],
			["name" => "zulrah", "alias" => "Zulrah", "type" => "boss", "model" => "App\Boss\Zulrah"],
			["name" => "dagannoth kings", "alias" => "Dagannoth Kings", "type" => "boss", "model" => "App\Boss\DagannothKings"],
			// ["name" => "the fight caves", "alias" => "The Fight caves", "type" => "boss", "model" => "App\Boss\TheFightCaves"],
			// ["name" => "the inferno", "alias" => "something", "type" => "boss", "model" => "App\Boss\TheInferno"],
			["name" => "the nightmare", "alias" => "The Nightmare", "type" => "boss", "model" => "App\Boss\TheNightmare"],
			["name" => "goblin", "alias" => "Goblin", "type" => "boss", "model" => "App\Boss\Goblin"], // TODO remove later
		]);
    }
}
