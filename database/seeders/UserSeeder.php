<?php

namespace Database\Seeders;

use App\Account;
use App\Collection;
use App\Helpers\Helper;
use App\Skill;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Simon',
            'email' => 'simon@runemanager.com',
            'password' => bcrypt('runemanager1234'),
            'icon_id' => Helper::randomItemId(true),
        ]);

        User::create([
            'name' => 'Simon2',
            'email' => 'simon2@runemanager.com',
            'password' => bcrypt('runemanager1234'),
            'icon_id' => Helper::randomItemId(true),
        ]);

        User::create([
            'name' => 'Simon3',
            'email' => 'simon3@runemanager.com',
            'password' => bcrypt('runemanager1234'),
            'icon_id' => Helper::randomItemId(true),
        ]);

        $accounts = ["Jern Zlimon", "IronicOcelot", "Mmorpg"];

        foreach (User::get() as $userKey => $user) {
            $playerDataUrl = 'https://secure.runescape.com/m=hiscore_oldschool/index_lite.ws?player=' . str_replace(
                    ' ',
                    '%20',
                    $accounts[$userKey]
                );

            /* Get the $playerDataUrl file content. */
            $playerData = Helper::getPlayerData($playerDataUrl);

            if (!$playerData) {
                return response("Could not fetch player data from hiscores", 406);
            }

            DB::beginTransaction();

            try {
                $account = Account::create(
                    [
                        'user_id' => $user->id,
                        'account_type' => Helper::listAccountTypes()[rand(0, 3)],
                        'username' => ucfirst($accounts[$userKey]),
                        'rank' => $playerData[0][0],
                        'level' => $playerData[0][1],
                        'xp' => $playerData[0][2]
                    ]
                );
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

            $skills = Helper::listSkills();
            $skillsCount = count($skills);

            foreach ($skills as $key => $skill) {
                $skill = Skill::where('name', $skill)->firstOrFail();

                $skill = new $skill->model;

                $skill->account_id = $account->id;
                $skill->rank = ($playerData[$key + 1][0] >= 1 ? $playerData[$key + 1][0] : 0);
                $skill->level = $playerData[$key + 1][1];
                $skill->xp = ($playerData[$key + 1][2] >= 0 ? $playerData[$key + 1][2] : 0);

                try {
                    $skill->save();
                } catch (\Exception $e) {
                    DB::rollback();
                    throw $e;
                }
            }

            $clues = Helper::listClueScrollTiers();
            $cluesCount = count($clues);
            $cluesIndex = 0;

            for ($i = ($skillsCount + 3); $i < ($skillsCount + 3 + $cluesCount); $i++) {
                $clueCollection = Collection::where('slug', $clues[$cluesIndex] . '-treasure-trails')->firstOrFail();

                $clueCollection = new $clueCollection->model;

                $clueCollection->account_id = $account->id;
                $clueCollection->kill_count = ($playerData[$i + 1][1] >= 0 ? $playerData[$i + 1][1] : 0);
                $clueCollection->rank = ($playerData[$i + 1][0] >= 0 ? $playerData[$i + 1][0] : 0);

                try {
                    $clueCollection->save();
                } catch (\Exception $e) {
                    DB::rollback();
                    throw $e;
                }

                $cluesIndex++;
            }

            $bosses = Helper::listBosses();
            array_splice($bosses, 13, 1);
            $bossIndex = 0;

            $dksKillCount = 0;

            for ($i = ($skillsCount + $cluesCount + 5); $i < ($skillsCount + $cluesCount + 5 + count($bosses)); $i++) {
                $bossCollection = Collection::where('slug', $bosses[$bossIndex])->firstOrFail();

                $bossCollection = new $bossCollection->model;

                $bossCollection->account_id = $account->id;
                $bossCollection->kill_count = ($playerData[$i + 1][1] >= 0 ? $playerData[$i + 1][1] : 0);
                $bossCollection->rank = ($playerData[$i + 1][0] >= 0 ? $playerData[$i + 1][0] : 0);

                if (in_array(
                    $bosses[$bossIndex],
                    ['dagannoth prime', 'dagannoth-rex', 'dagannoth-supreme'],
                    true
                )) {
                    $dksKillCount += ($playerData[$i + 1][1] >= 0 ? $playerData[$i + 1][1] : 0);
                }

                try {
                    $bossCollection->save();
                } catch (\Exception $e) {
                    DB::rollback();
                    throw $e;
                }

                $bossIndex++;
            }

            /**
             * Since there are no official total kill count hiscore for
             * DKS' and we are going to retrieve loot for them from the
             * collection log, we have to manually create a table.
             * This might also happen with other bosses in the future
             * that share collection log entry, but have separate hiscores.
             */
            $dks = new \App\Boss\DagannothKings;

            $dks->account_id = $account->id;
            $dks->kill_count = $dksKillCount;

            try {
                $dks->save();
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

            $npcs = Helper::listNpcs();

            foreach ($npcs as $npc) {
                $npcCollection = Collection::findByNameAndCategory($npc, 4);

                $npcCollection = new $npcCollection->model;

                $npcCollection->account_id = $account->id;

                try {
                    $npcCollection->save();
                } catch (\Exception $e) {
                    DB::rollback();
                    throw $e;
                }
            }

            DB::commit();
        }
    }
}
