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
			["name" => "chaos elemental", "collection_type" => "App\Boss\ChaosElemental"],
			["name" => "chaos fanatic", "collection_type" => "App\Boss\ChaosFanatic"],
			["name" => "commander zilyana", "collection_type" => "App\Boss\CommanderZilyana"],
			["name" => "corporeal beast", "collection_type" => "App\Boss\CorporealBeast"],
			["name" => "crazy archaeologist", "collection_type" => "App\Boss\CrazyArchaeologist"],
			["name" => "dagannoth kings", "collection_type" => "App\Boss\DagannothKings"],
			["name" => "the fight caves", "collection_type" => "App\Boss\TheFightCaves"],
			["name" => "the gauntlet", "collection_type" => "App\Boss\TheGauntlet"],
			["name" => "general graardor", "collection_type" => "App\Boss\GeneralGraardor"],
			["name" => "giant mole", "collection_type" => "App\Boss\GiantMole"],
			["name" => "grotesque guardians", "collection_type" => "App\Boss\GrotesqueGuardians"],
			["name" => "hespori", "collection_type" => "App\Boss\Hespori"],
			["name" => "the inferno", "collection_type" => "App\Boss\TheInferno"],
			["name" => "kalphite queen", "collection_type" => "App\Boss\KalphiteQueen"],
			["name" => "king black dragon", "collection_type" => "App\Boss\KingBlackDragon"],
			["name" => "kraken", "collection_type" => "App\Boss\Kraken"],
			["name" => "kreearra", "collection_type" => "App\Boss\KreeArra"],
			["name" => "kril tsutsaroth", "collection_type" => "App\Boss\KrilTsutsaroth"],
			["name" => "the nightmare", "collection_type" => "App\Boss\TheNightmare"],
			["name" => "obor", "collection_type" => "App\Boss\Obor"],
			["name" => "sarachnis", "collection_type" => "App\Boss\Sarachnis"],
			["name" => "scorpia", "collection_type" => "App\Boss\Scorpia"],
			["name" => "skotizo", "collection_type" => "App\Boss\Skotizo"],
			["name" => "thermonuclear smoke devil", "collection_type" => "App\Boss\ThermonuclearSmokeDevil"],
			["name" => "venenatis", "collection_type" => "App\Boss\Venenatis"],
			["name" => "vetion", "collection_type" => "App\Boss\Vetion"],
			["name" => "vorkath", "collection_type" => "App\Boss\Vorkath"],
			["name" => "wintertodt", "collection_type" => "App\Boss\Wintertodt"],
			["name" => "zalcano", "collection_type" => "App\Boss\Zalcano"],
			["name" => "zulrah", "collection_type" => "App\Boss\Zulrah"],
		]);
    }
}
