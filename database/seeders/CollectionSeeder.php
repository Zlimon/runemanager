<?php

namespace Database\Seeders;

use App\Clients\CollectionLogClient;
use App\Models\Category;
use App\Traits\CollectionTrait;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CollectionSeeder extends Seeder
{
    use CollectionTrait;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * Categories:
         * 1 - skill
         * 2 - pvp
         * 3 - clue
         * 4 - minigame
         * 5 - boss
         * 6 - raid
         * 7 - npc
         */
        $collections = [
            'pvp' => [
                'bounty-hunter' => 'Bounty Hunter - Hunter',
                'bounty-hunter-rogue' => 'Bounty Hunter - Rogue',
                'bounty-hunter-legacy' => 'Bounty Hunter (Legacy) - Hunter',
                'bounty-hunter-rogue-legacy' => 'Bounty Hunter (Legacy) - Rogue',
                'unknown-1' => 'Unknown 1',
                'unknown-2' => 'Unknown 2',
            ],
            'clue' => [
                'all-treasure-trails' => 'All Treasure Trails',
                'beginner-treasure-trails' => 'Beginner Treasure Trails',
                'easy-treasure-trails' => 'Easy Treasure Trails',
                'medium-treasure-trails' => 'Clue scroll (medium)',
                'hard-treasure-trails' => 'Hard Treasure Trails',
                'elite-treasure-trails' => 'Elite Treasure Trails',
                'master-treasure-trails' => 'Master Treasure Trails',
            ],
            'minigame' => [
                'last-man-standing' => 'Last Man Standing',
                'pvp-arena' => 'PvP Arena',
                'soul-wars-zeal' => 'Soul Wars Zeal',
                'rifts-closed' => 'Rifts closed',
                'colosseum-glory' => 'Colosseum Glory',
            ],
            'boss' => [
                'abyssal-sire' => 'Abyssal Sire',
                'alchemical-hydra' => 'Alchemical Hydra',
                'artio' => 'Artio',
                'barrows-chests' => 'Barrows Chests',
                'bryophyta' => 'Bryophyta',
                'callisto' => 'Callisto',
                'calvarion' => 'Calvar\'ion',
                'cerberus' => 'Cerberus',
                'chambers-of-xeric' => 'Chambers of Xeric - Placeholder',
                'chambers-of-xeric-challenge-mode' => 'Chambers of Xeric: Challenge Mode - Placeholder',
                'chaos-elemental' => 'Chaos Elemental',
                'chaos-fanatic' => 'Chaos Fanatic',
                'commander-zilyana' => 'Commander Zilyana',
                'corporeal-beast' => 'Corporeal Beast',
                'crazy-archaeologist' => 'Crazy Archaeologist',
                'dagannoth-prime' => 'Dagannoth Prime',
                'dagannoth-rex' => 'Dagannoth Rex',
                'dagannoth-supreme' => 'Dagannoth Supreme',
                'deranged-archaeologist' => 'Deranged Archaeologist',
                'duke-sucellus' => 'Duke Sucellus',
                'general-graardor' => 'General Graardor',
                'giant-mole' => 'Giant Mole',
                'grotesque-guardians' => 'Grotesque Guardians',
                'hespori' => 'Hespori',
                'kalphite-queen' => 'Kalphite Queen',
                'king-black-dragon' => 'King Black Dragon',
                'kraken' => 'Kraken',
                'kreearra' => 'Kree\'arra',
                'kril-tsutsaroth' => 'K\'ril Tsutsaroth',
                'lunar-chests' => 'Lunar Chests',
                'mimic' => 'Mimic',
                'nex' => 'Nex',
                'nightmare' => 'Nightmare',
                'phosanis-nightmare' => 'Phosani\'s Nightmare',
                'obor' => 'Obor',
                'phantom-muspah' => 'Phantom Muspah',
                'sarachnis' => 'Sarachnis',
                'scorpia' => 'Scorpia',
                'scurrius' => 'Scurrius',
                'skotizo' => 'Skotizo',
                'unknown-3' => 'Unknown 3',
                'spindel' => 'Spindel',
                'tempoross' => 'Tempoross',
                'the-gauntlet' => 'The Gauntlet',
                'the-corrupted-gauntlet' => 'The Corrupted Gauntlet',
                'the-leviathan' => 'The Leviathan',
                'unknown-4' => 'Unknown 4',
                'theatre-of-blood' => 'Theatre of Blood - Placeholder',
                'theatre-of-blood-hard-mode' => 'Theatre of Blood: Hard Mode - Placeholder',
                'thermonuclear-smoke-devil' => 'Thermonuclear Smoke Devil',
                'tombs-of-amascut' => 'Tombs of Amascut - Placeholder',
                'tombs-of-amascut-expert-mode' => 'Tombs of Amascut: Expert Mode - Placeholder',
                'tzkal-zuk' => 'TzKal-Zuk',
                'tztok-jad' => 'TzTok-Jad',
                'unknown-5' => 'Unknown 5',
                'venenatis' => 'Venenatis',
                'vetion' => 'Vet\'ion',
                'vorkath' => 'Vorkath',
                'wintertodt' => 'Wintertodt',
                'zalcano' => 'Zalcano',
                'zulrah' => 'Zulrah',
            ],
            'raid' => [
                'chambers-of-xeric' => 'Chambers of Xeric',
                'chambers-of-xeric-challenge-mode' => 'Chambers of Xeric: Challenge Mode',
                'theatre-of-blood' => 'Theatre of Blood',
                'theatre-of-blood-hard-mode' => 'Theatre of Blood: Hard Mode',
                'tombs-of-amascut' => 'Tombs of Amascut',
                'tombs-of-amascut-expert-mode' => 'Tombs of Amascut: Expert Mode',
            ]
        ];

        $collectionLogClient = new CollectionLogClient();

        // Get number 1 player on collectionlog.net hiscores to get all collection log pages
        try {
            $response = $collectionLogClient->request('GET', '/hiscores/1?accountType=NORMAL');

            $result = json_decode($response->getBody()->getContents(), true);

            if (!isset($result['hiscores'][0]['username'])) {
                throw new Exception('Could not retrieve rank 1 player from collectionlog.net hiscores.');
            }
        } catch (Exception $e) {
            $this->command->warn($e->getMessage());

            return;
        }

        $rankOne = $result['hiscores'][0]['username'];

        // Get all collection log entries for rank 1 player
        try {
            $response = $collectionLogClient->request('GET', '/collectionlog/user/' . $rankOne);
        } catch (Exception $e) {
            $this->command->warn($e->getMessage());

            return;
        }

        $result = json_decode($response->getBody()->getContents(), true);



        foreach ($collections as $category => $collection) {
            $category = Category::whereSlug($category)->first();

            foreach ($collection as $slug => $name) {
                // collectionLogTab is the collection name on collectionlog.net
                switch ($category->slug) {
                    case 'clue':
                        $collectionLogTab = 'Clues';
                        break;
                    case 'minigame':
                        $collectionLogTab = 'Minigames';
                        break;
                    case 'boss':
                        $collectionLogTab = 'Bosses';
                        break;
                    case 'raid':
                        $collectionLogTab = 'Raids';
                        break;
                    default:
                        $collectionLogTab = $category->slug;
                        break;
                }

                if (isset($result['collectionLog']['tabs'][$collectionLogTab][$name])) {
                    try {
                        $this->createHiscore($category, $name, $result['collectionLog']['tabs'][$collectionLogTab][$name]['items']);
                    } catch (Exception $e) {
                        $this->command->warn($e->getMessage());

                        continue;
                    }
                }

                // Find respective entry in collectionlog.net hiscores and create hiscore entry with items
//                dd($result['collectionLog']['tabs'][$collectionLogTab]);

//                try {
//                    $this->createHiscore($category, $name);
//                } catch (Exception $e) {
//                    $this->command->warn($e->getMessage());
//
//                    continue;
//                }
            }
        }
    }
}
