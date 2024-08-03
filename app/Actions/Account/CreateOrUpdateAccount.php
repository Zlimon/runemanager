<?php

namespace App\Actions\Account;

use App\Enums\AccountTypesEnum;
use App\Models\Account;
use App\Models\Collection;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateOrUpdateAccount
{
    /**
     * @param string $accountUsername
     * @param User $user
     * @param AccountTypesEnum $accountType
     * @return Account
     * @throws \Exception
     */
    public function createOrUpdateAccount(string $accountUsername, User $user, AccountTypesEnum $accountType = AccountTypesEnum::NORMAL): Account
    {
        $playerData = $this->getPlayerData(sprintf("https://secure.runescape.com/m=hiscore_oldschool/index_lite.ws?player=%s", urlencode($accountUsername)));

        if (empty($playerData)) {
            throw new \Exception(sprintf("Could not retrieve player data for '%s' from the official hiscores.", $accountUsername));
        }

        Db::beginTransaction();

        try {
            $account = Account::whereUsername($accountUsername)->first();

            if (!$account) {
                $account = new Account();
            }

            $account->user_id = $user->id;
            $account->account_type = $accountType;
            $account->username = $accountUsername;
            $account->rank = $playerData[0][0];
            $account->level = $playerData[0][1];
            $account->xp = $playerData[0][2];

            $account->save();
        } catch (\Exception $e) {
            Db::rollback();

            throw new \Exception(sprintf("Could not create or update account '%s'. Message: %s", $accountUsername, $e->getMessage()));
        }

        Db::commit();

        try {
            $this->createOrUpdateAccountHiscores($account, $playerData);
        } catch (\Exception $e) {
            throw new \Exception(sprintf("Could not create or update account hiscores for '%s'. Message: %s", $accountUsername, $e->getMessage()));
        }

        return $account;
    }

    /**
     * @param string $url
     * @return array
     */
    public function getPlayerData(string $url): array
    {
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

        /* Get the content of $url. */
        $response = curl_exec($handle);

        /* Check for errors (content not found). */
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        /* If the document has loaded successfully without any redirection or error */
        if ($httpCode >= 200 && $httpCode < 300) {
            $playerStats = explode("\n", $response);

            if (count($playerStats) > 1) {
                /* Convert the CSV file of player stats into an array */
                $playerData = [];
                foreach ($playerStats as $playerStat) {
                    $playerData[] = str_getcsv($playerStat);
                }

                if ($playerData[0][0]) {
                    return $playerData;
                } else {
                    return [];
                }
            } else {
                return [];
            }
        } else {
            return [];
        }
    }

    /**
     * @param Account $account
     * @param array $playerData
     * @return Account
     * @throws \Exception
     */
    public function createOrUpdateAccountHiscores(Account $account, array $playerData): Account
    {
        DB::beginTransaction();

        $models = [
            'skills' => array_merge(['overall'], Skill::pluck('slug')->all()),
            'pvp' => Collection::byCategorySlug('pvp')->pluck('slug')->all(),
            'clues' => Collection::byCategorySlug('clue')->pluck('slug')->all(),
            'minigame' => Collection::byCategorySlug('minigame')->pluck('slug')->all(),
            'bosses' => Collection::byCategorySlug('boss')->pluck('slug')->all(),
        ];

        $keys = array_merge($models['skills'], $models['pvp'], $models['clues'], $models['minigame'], $models['bosses']);

        $combined = [];
        foreach ($keys as $index => $skill) {
            if (isset($playerData[$index])) {
                $combined[$skill] = array_combine(range(1, count($playerData[$index])), $playerData[$index]);
            }
        }

        $skills = Skill::all();

        foreach ($skills as $key => $skill) {
            $accountSkill = $account->skill($skill)->first();

            if (!$accountSkill) {
                $accountSkill = new $skill->model;
            }

            $accountSkill->account_id = $account->id;
            $accountSkill->rank = ($playerData[$key + 1][0] >= 1 ? $playerData[$key + 1][0] : 0);
            $accountSkill->level = $playerData[$key + 1][1];
            $accountSkill->xp = ($playerData[$key + 1][2] >= 0 ? $playerData[$key + 1][2] : 0);

            try {
                $accountSkill->save();
            } catch (\Exception $e) {
                DB::rollback();

                throw $e;
            }
        }

        DB::commit();

        // Currently not supporting other hiscores than skills. Other hiscores are handled by collectionlog.net API
        return $account;

        $skillsCount = count($skills);

//        $miniGames = Collection::byCategorySlug('minigame')->pluck('slug')->all();
        $miniGames = ['bounty-hunter', 'bounty-hunter-rogues', 'lms', 'soul-wars', 'castle-wars', 'clan-wars'];
        $miniGamesCount = count($miniGames);

        $clues = Collection::byCategorySlug('clue')->pluck('slug')->all();
        $cluesCount = count($clues);
        $cluesIndex = 0;

        for ($i = ($skillsCount + $miniGamesCount); $i < ($skillsCount + $miniGamesCount + $cluesCount); $i++) {
            $clueCollection = Collection::where('slug', $clues[$cluesIndex])->first();

            $accountClueCollection = $account->collection($clueCollection)->first();

            if (!$accountClueCollection) {
                $accountClueCollection = new $clueCollection->model;
            }

            $accountClueCollection->account_id = $account->id;
            $accountClueCollection->kill_count = ($playerData[$i + 1][1] >= 0 ? $playerData[$i + 1][1] : 0);
            $accountClueCollection->rank = ($playerData[$i + 1][0] >= 0 ? $playerData[$i + 1][0] : 0);

            try {
                $accountClueCollection->save();
            } catch (\Exception $e) {
                DB::rollback();

                throw $e;
            }

            $cluesIndex++;
        }

        $bosses = Collection::byCategorySlug('boss')->pluck('slug')->all();
        array_splice($bosses, 13, 1);
        $bossCount = count($bosses);
        $bossIndex = 0;

        $dksKillCount = 0;

        for ($i = ($skillsCount + $miniGamesCount + $cluesCount); $i < ($skillsCount + $cluesCount + $miniGamesCount + $bossCount); $i++) {
            $bossCollection = Collection::where('slug', $bosses[$bossIndex])->first();

            $accountBossCollection = $account->collection($bossCollection)->first();

            if (!$accountBossCollection) {
                $accountBossCollection = new $bossCollection->model;
            }

            $accountBossCollection->account_id = $account->id;
            $accountBossCollection->kill_count = ($playerData[$i + 1][1] >= 0 ? $playerData[$i + 1][1] : 0);
            $accountBossCollection->rank = ($playerData[$i + 1][0] >= 0 ? $playerData[$i + 1][0] : 0);

            if (in_array(
                $bosses[$bossIndex],
                ['dagannoth prime', 'dagannoth rex', 'dagannoth supreme'],
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
        $dks = $account->collection(Collection::where('slug', 'dagannoth-kings')->first())->first();

        if (!$dks) {
            $dks = new \App\Models\Boss\DagannothKings;
        }

        $dks->account_id = $account->id;
        $dks->kill_count = $dksKillCount;

        try {
            $dks->save();
        } catch (\Exception $e) {
            DB::rollback();

            throw $e;
        }

//        $npcs = Collection::byCategorySlug('npc')->pluck('slug')->all();
//
//        foreach ($npcs as $npc) {
//            $npcCollection = Collection::findByNameAndCategory($npc, 4);
//
//            $accountNpcCollection = $account->collection($npcCollection)->first();
//
//            if (!$accountNpcCollection) {
//                $accountNpcCollection = new $npcCollection->model;
//            }
//
//            $accountNpcCollection->account_id = $account->id;
//
//            try {
//                $accountNpcCollection->save();
//            } catch (\Exception $e) {
//                DB::rollback();
//
//                throw $e;
//            }
//        }

        DB::commit();

        return $account;
    }
}
