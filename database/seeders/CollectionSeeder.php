<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Collection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class CollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
        $collections = [
            'clue' => [
                'All Treasure Trails' => 'all-treasure-trails',
                'Clue scroll (beginner)' => 'beginner-treasure-trails',
                'Clue scroll (easy)' => 'easy-treasure-trails',
                'Clue scroll (medium)' => 'medium-treasure-trails',
                'Clue scroll (hard)' => 'hard-treasure-trails',
                'Clue scroll (elite)' => 'elite-treasure-trails',
                'Clue scroll (master)' => 'master-treasure-trails',
            ],
            'minigame' => [
                'Bounty Hunter - Hunter' => 'bounty-hunter',
                'Bounty Hunter - Rogue' => 'bounty-hunter-rogue',
                'Bounty Hunter (Legacy) - Hunter' => 'bounty-hunter-legacy',
                'Bounty Hunter (Legacy) - Rogue' => 'bounty-hunter-rogues-legacy',
                'Last Man Standing' => 'last-man-standing',
                'PvP Arena' => 'pvp-arena',
                'Soul Wars Zeal' => 'soul-wars-zeal',
                'Rifts closed' => 'rifts-closed',
                'Colosseum Glory' => 'colosseum-glory',
            ],
            'boss' => [
                'Abyssal Sire' => 'abyssal-sire',
                'Alchemical Hydra' => 'alchemical-hydra',
                'Artio' => 'artio',
                'Barrows Chests' => 'barrows-chests',
                'Bryophyta' => 'bryophyta',
                'Callisto' => 'callisto',
                'Calvar\'ion' => 'calvarion',
                'Cerberus' => 'cerberus',
//                'Chambers of Xeric' => 'chambers-of-xeric',
//                'Chambers of Xeric: Challenge Mode' => 'chambers-of-xeric-challenge-mode',
                'Chaos Elemental' => 'chaos-elemental',
                'Chaos Fanatic' => 'chaos-fanatic',
                'Commander Zilyana' => 'commander-zilyana',
                'Corporeal Beast' => 'corporeal-beast',
                'Crazy Archaeologist' => 'crazy-archaeologist',
//                'Dagannoth Kings' => 'dagannoth-kings',
                'Dagannoth Prime' => 'dagannoth-prime',
                'Dagannoth Rex' => 'dagannoth-rex',
                'Dagannoth Supreme' => 'dagannoth-supreme',
                'Deranged Archaeologist' => 'deranged-archaeologist',
                'General Graardor' => 'general-graardor',
                'Giant Mole' => 'giant-mole',
                'Grotesque Guardians' => 'grotesque-guardians',
                'Hespori' => 'hespori',
                'Kalphite Queen' => 'kalphite-queen',
                'King Black Dragon' => 'king-black-dragon',
                'Kraken' => 'kraken',
                'Kree\'arra' => 'kreearra',
                'K\'ril Tsutsaroth' => 'kril-tsutsaroth',
                'Lunar Chests' => 'lunar-chests',
                'Mimic' => 'mimic',
                'Nex' => 'nex',
                'Nightmare' => 'nightmare',
                'Phosani\'s Nightmare' => 'phosanis-nightmare',
                'Obor' => 'obor',
                'Phantom Muspah' => 'phantom-muspah',
                'Sarachnis' => 'sarachnis',
                'Scorpia' => 'scorpia',
                'Scurrius' => 'scurrius',
                'Skotizo' => 'skotizo',
                'Spindel' => 'spindel',
                'Tempoross' => 'tempoross',
                'The Gauntlet' => 'the-gauntlet',
                'The Corrupted Gauntlet' => 'the-corrupted-gauntlet',
                'The Leviathan' => 'the-leviathan',
//                'Theatre of Blood' => 'theatre-of-blood',
//                'Theatre of Blood: Hard Mode' => 'theatre-of-blood-hard-mode',
                'Thermonuclear Smoke Devil' => 'thermonuclear-smoke-devil',
//                'Tombs of Amascut' => 'tombs-of-amascut',
//                'Tombs of Amascut: Expert Mode' => 'tombs-of-amascut-expert-mode',
                'TzKal-Zuk' => 'tzkal-zuk',
                'TzTok-Jad' => 'tztok-jad',
                'Venenatis' => 'venenatis',
                'Vet\'ion' => 'vetion',
                'Vorkath' => 'vorkath',
                'Wintertodt' => 'wintertodt',
                'Zalcano' => 'zalcano',
                'Zulrah' => 'zulrah',
            ],
            'raid' => [
                'Chambers of Xeric' => 'chambers-of-xeric',
                'Chambers of Xeric: Challenge Mode' => 'chambers-of-xeric-challenge-mode',
                'Theatre of Blood' => 'theatre-of-blood',
                'Theatre of Blood: Hard Mode' => 'theatre-of-blood-hard-mode',
                'Tombs of Amascut' => 'tombs-of-amascut',
                'Tombs of Amascut: Expert Mode' => 'tombs-of-amascut-expert-mode',
            ]
        ];

        foreach ($collections as $category => $collection) {
            $categoryId = Category::whereCategory($category)->first()->id;
            $order = $categoryId * 1000;

            foreach ($collection as $name => $slug) {
                $model = sprintf("App\Models\%s\%s", ucfirst($category), Str::of($slug)->studly());

                if (!class_exists($model)) {
                    $this->command->warn(sprintf("Model %s does not exist. Creating...", $model));

                    Artisan::call('hiscore:create', [
                        'type' => $category,
                        'name' => Str::snake($slug),
                    ]);

                    $newestCollection = Collection::whereCategoryId($categoryId)->orderByDesc('order')->pluck('order')->first();

                    if ($newestCollection) {
                        $order = ++$newestCollection;
                    }

                    continue;
                }

                Collection::create([
                    'category_id' => $categoryId,
                    'order' => $order,
                    'name' => $name,
                    'slug' => $slug,
                    'model' => $model,
                ]);

                $order++;
            }
        }
    }
}
