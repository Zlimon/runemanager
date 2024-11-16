<?php

namespace App\Actions\Account;

use App\Enums\AccountTypesEnum;
use App\Helpers\HiscoreHelper;
use App\Models\Account;
use App\Models\Collection;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateOrUpdateAccount
{
    /**
     * @throws \Exception
     */
    public function createOrUpdateAccount(string $accountUsername, User $user, AccountTypesEnum $accountType = AccountTypesEnum::NORMAL): Account
    {
        $playerData = $this->getPlayerData(sprintf('https://secure.runescape.com/m=hiscore_oldschool/index_lite.ws?player=%s', urlencode($accountUsername)));

        if (empty($playerData)) {
            throw new \Exception(sprintf("Could not retrieve player data for '%s' from the official hiscores.", $accountUsername));
        }

        Db::beginTransaction();

        try {
            $account = Account::whereUsername($accountUsername)->first();

            if (! $account) {
                $account = new Account;
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
            DB::rollback();

            throw new \Exception(sprintf("Could not create or update account hiscores for '%s'. Message: %s", $accountUsername, $e->getMessage()));
        }

        return $account;
    }

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
     * @throws \Exception
     */
    public function createOrUpdateAccountHiscores(Account $account, array $playerData): Account
    {
        DB::beginTransaction();

        $skills = Skill::all();
        $skillsCount = count($skills);

        foreach ($skills as $key => $skill) {
            $accountSkill = $account->skill($skill)->first();

            if (! $accountSkill) {
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

        $pvp = array_keys(HiscoreHelper::pvp());
        $pvpCount = count($pvp) + 2; // + 2 for unknown hiscore entries TODO Need to add to $pvp if populating PvP stats

        // TODO Add PvP stats
        $pvpIndex = 0;

        $clues = array_keys(HiscoreHelper::clue());
        $clueCount = count($clues);

        $cluesIndex = 0;
        for ($i = ($skillsCount + $pvpCount); $i < ($skillsCount + $pvpCount + $clueCount); $i++) {
            $clueCollection = Collection::whereSlug($clues[$cluesIndex])->first();

            if (! $clueCollection) {
                DB::rollback();

                throw new \Exception(sprintf("Could not find collection '%s'.", $clues[$cluesIndex]));
            }

            $accountClueCollection = $account->collection($clueCollection)->first();

            if (! $accountClueCollection) {
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

        $minigames = array_keys(HiscoreHelper::minigame());
        $minigameCount = count($minigames);

        // TODO Add minigame stats
        $minigameIndex = 0;

        $bosses = array_keys(HiscoreHelper::boss(true));
        $bossCount = count($bosses);

        $bossIndex = 0;
        for ($i = ($skillsCount + $pvpCount + $clueCount + $minigameCount); $i < ($skillsCount + $pvpCount + $clueCount + $minigameCount + $bossCount); $i++) {
            $bossCollection = Collection::whereSlug($bosses[$bossIndex])->first();

            if (! $bossCollection) {
                DB::rollback();

                throw new \Exception(sprintf("Could not find collection '%s'.", $bosses[$bossCount]));
            }

            $accountBossCollection = $account->collection($bossCollection)->first();

            if (! $accountBossCollection) {
                $accountBossCollection = new $bossCollection->model;
            }

            $accountBossCollection->account_id = $account->id;
            $accountBossCollection->kill_count = ($playerData[$i + 1][1] >= 0 ? $playerData[$i + 1][1] : 0);
            $accountBossCollection->rank = ($playerData[$i + 1][0] >= 0 ? $playerData[$i + 1][0] : 0);

            try {
                $accountBossCollection->save();
            } catch (\Exception $e) {
                DB::rollback();

                throw $e;
            }

            $bossIndex++;
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
