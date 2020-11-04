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
			["name" => "abyssal sire", "display_name" => "Abyssal Sire", "type" => "boss", "model" => "App\Boss\AbyssalSire"],
			["name" => "alchemical hydra", "display_name" => "Alchemical Hydra", "type" => "boss", "model" => "App\Boss\AlchemicalHydra"],
			["name" => "barrows chests", "display_name" => "Barrows Chests", "type" => "boss", "model" => "App\Boss\BarrowsChests"],
			["name" => "bryophyta", "display_name" => "Bryophyta", "type" => "boss", "model" => "App\Boss\Bryophyta"],
			["name" => "callisto", "display_name" => "Callisto", "type" => "boss", "model" => "App\Boss\Callisto"],
			["name" => "cerberus", "display_name" => "Cerberus", "type" => "boss", "model" => "App\Boss\Cerberus"],
			["name" => "chambers of xeric", "display_name" => "Chambers of Xeric", "type" => "raid", "model" => "App\Raid\ChambersOfXeric"],
			["name" => "chambers of xeric challenge mode", "display_name" => "COX: Challenge Mode", "type" => "raid", "model" => "App\Raid\ChambersOfXericChallengeMode"],
			["name" => "chaos elemental", "display_name" => "Chaos Elemental", "type" => "boss", "model" => "App\Boss\ChaosElemental"],
			["name" => "chaos fanatic", "display_name" => "Chaos Fanatic", "type" => "boss", "model" => "App\Boss\ChaosFanatic"],
			["name" => "commander zilyana", "display_name" => "Commander Zilyana", "type" => "boss", "model" => "App\Boss\CommanderZilyana"],
			["name" => "corporeal beast", "display_name" => "Corporeal Beast", "type" => "boss", "model" => "App\Boss\CorporealBeast"],
			["name" => "crazy archaeologist", "display_name" => "Crazy Archaeologist", "type" => "boss", "model" => "App\Boss\CrazyArchaeologist"],
			["name" => "dagannoth prime", "display_name" => "Dagannoth Prime", "type" => "boss", "model" => "App\Boss\DagannothPrime"],
			["name" => "dagannoth rex", "display_name" => "Dagannoth Rex", "type" => "boss", "model" => "App\Boss\DagannothRex"],
			["name" => "dagannoth supreme", "display_name" => "Dagannoth Surpeme", "type" => "boss", "model" => "App\Boss\DagannothSupreme"],
			["name" => "deranged archaeologist", "display_name" => "Deranged Archaeologist", "type" => "boss", "model" => "App\Boss\DerangedArchaeologist"],
			["name" => "general graardor", "display_name" => "General Graardor", "type" => "boss", "model" => "App\Boss\GeneralGraardor"],
			["name" => "giant mole", "display_name" => "Giant Mole", "type" => "boss", "model" => "App\Boss\GiantMole"],
			["name" => "grotesque guardians", "display_name" => "Grotesque Guardians", "type" => "boss", "model" => "App\Boss\GrotesqueGuardians"],
			["name" => "hespori", "display_name" => "Hespori", "type" => "boss", "model" => "App\Boss\Hespori"],
			["name" => "kalphite queen", "display_name" => "Kalphite Queen", "type" => "boss", "model" => "App\Boss\KalphiteQueen"],
			["name" => "king black dragon", "display_name" => "King Black Dragon", "type" => "boss", "model" => "App\Boss\KingBlackDragon"],
			["name" => "kraken", "display_name" => "Kraken", "type" => "boss", "model" => "App\Boss\Kraken"],
			["name" => "kreearra", "display_name" => "Kree'arra", "type" => "boss", "model" => "App\Boss\KreeArra"],
			["name" => "kril tsutsaroth", "display_name" => "K'ril Tsutsaroth", "type" => "boss", "model" => "App\Boss\KrilTsutsaroth"],
			["name" => "mimic", "display_name" => "Mimic", "type" => "boss", "model" => "App\Boss\Mimic"],
			// ["name" => "nightmare", "display_name" => "Nightmare", "type" => "boss", "model" => "App\Boss\TheNightmare"],
			["name" => "obor", "display_name" => "Obor", "type" => "boss", "model" => "App\Boss\Obor"],
			["name" => "sarachnis", "display_name" => "Sarachnis", "type" => "boss", "model" => "App\Boss\Sarachnis"],
			["name" => "scorpia", "display_name" => "Scorpia", "type" => "boss", "model" => "App\Boss\Scorpia"],
			["name" => "skotizo", "display_name" => "Skotizo", "type" => "boss", "model" => "App\Boss\Skotizo"],
			["name" => "the gauntlet", "display_name" => "The Gauntlet", "type" => "boss", "model" => "App\Boss\TheGauntlet"],
			["name" => "the corrupted gauntlet", "display_name" => "The Corrupted Gauntlet", "type" => "boss", "model" => "App\Boss\TheCorruptedGauntlet"],
			["name" => "theatre of blood", "display_name" => "Theatre of Blood", "type" => "raid", "model" => "App\Raid\TheatreOfBlood"],
			["name" => "thermonuclear smoke devil", "display_name" => "Thermonuclear Smoke Devil", "type" => "boss", "model" => "App\Boss\ThermonuclearSmokeDevil"],
			["name" => "tzkal zuk", "display_name" => "TzKal-Zuk", "type" => "boss", "model" => "App\Boss\TheInferno"],
			["name" => "tztok jad", "display_name" => "TzTok-Jad", "type" => "boss", "model" => "App\Boss\TheFightCaves"],
			["name" => "venenatis", "display_name" => "Venenatis", "type" => "boss", "model" => "App\Boss\Venenatis"],
			["name" => "vetion", "display_name" => "Vetion", "type" => "boss", "model" => "App\Boss\Vetion"],
			["name" => "vorkath", "display_name" => "Vorkath", "type" => "boss", "model" => "App\Boss\Vorkath"],
			["name" => "wintertodt", "display_name" => "Wintertodt", "type" => "boss", "model" => "App\Boss\Wintertodt"],
			["name" => "zalcano", "display_name" => "Zalcano", "type" => "boss", "model" => "App\Boss\Zalcano"],
			["name" => "zulrah", "display_name" => "Zulrah", "type" => "boss", "model" => "App\Boss\Zulrah"],
			["name" => "dagannoth kings", "display_name" => "Dagannoth Kings", "type" => "boss", "model" => "App\Boss\DagannothKings"],
			// ["name" => "the fight caves", "display_name" => "The Fight caves", "type" => "boss", "model" => "App\Boss\TheFightCaves"],
			// ["name" => "the inferno", "display_name" => "something", "type" => "boss", "model" => "App\Boss\TheInferno"],
			["name" => "the nightmare", "display_name" => "The Nightmare", "type" => "boss", "model" => "App\Boss\TheNightmare"],
			["name" => "goblin", "display_name" => "something", "type" => "boss", "model" => "App\Boss\Goblin"], // TODO remove later
		]);
    }
}
