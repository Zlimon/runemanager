<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\AccountAuthStatus;
use App\Broadcast;
use App\Collection;
use App\Events\AccountOnline;
use App\Events\EventAll;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Log;
use App\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    /**
     * Create a new account instance after a valid registration.
     *
     * @param string $authCode
     * @return
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'username' => ['required', 'string', 'min:1', 'max:13'],
                'code' => ['required', 'string', 'min:1', 'max:8'],
                'account_type' => ['required', Rule::in(Helper::listAccountTypes())],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->messages()->all() as $value) {
                return response($value, 422);
            }
        }

        $accountUsername = request('username');

        $authStatus = AccountAuthStatus::where('username', $accountUsername)->where('status', 'pending')->first();
        if (!$authStatus) {
            return response($accountUsername . " has no pending status");
        }

        if ($authStatus->user_id !== auth()->user()->id) {
            return response($accountUsername . " is not linked to your user", 403);
        }

        if (request('account_type') !== $authStatus->account_type) {
            return response(
                "This account is registered as " . lcfirst(
                    Helper::formatAccountTypeName($authStatus->account_type)
                ) . ", not " . request('account_type'),
                406
            );
        }

        if (request('code') !== $authStatus->code) {
            return response("Invalid code", 406);
        }

        $playerDataUrl = 'https://secure.runescape.com/m=hiscore_oldschool/index_lite.ws?player=' . str_replace(
                ' ',
                '%20',
                $accountUsername
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
                    'user_id' => $authStatus->user_id,
                    'account_type' => request('account_type'),
                    'username' => ucfirst($accountUsername),
                    'rank' => $playerData[0][0],
                    'level' => $playerData[0][1],
                    'xp' => $playerData[0][2]
                ]
            );
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        try {
            $this->createOrUpdateAccountHiscores($account, $playerData);

            $authStatus->status = "success";

            try {
                $authStatus->save();
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();

        return response("Account successfully authenticated!", 201);
    }

    public function createOrUpdateAccountHiscores(Account $account, $playerData, $update = false)
    {
        $skills = Skill::get();
        $skillsCount = count($skills);

        foreach ($skills as $key => $skill) {
            if ($update) {
                $accountSkill = $account->skill($skill)->first();

                // If account is missing the skill, create new
                if (!$accountSkill) {
                    $accountSkill = new $skill->model;
                }
            } else {
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

        $clues = Helper::listClueScrollTiers();
        $cluesCount = count($clues);
        $cluesIndex = 0;

        for ($i = ($skillsCount + 3); $i < ($skillsCount + 3 + $cluesCount); $i++) {
            $collection = Collection::where('slug', $clues[$cluesIndex] . '-treasure-trails')->first();

            if ($update) {
                $accountClueCollection = $account->collection($collection)->first();

                // If account is missing the clue collection, create new
                if (!$accountClueCollection) {
                    $accountClueCollection = new $collection->model;
                }
            } else {
                $accountClueCollection = new $collection->model;
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

        $bosses = Helper::listBosses();
        array_splice($bosses, 13, 1);
        $bossIndex = 0;

        $dksKillCount = 0;

        for ($i = ($skillsCount + $cluesCount + 5); $i < ($skillsCount + $cluesCount + 5 + count($bosses)); $i++) {
            $collection = Collection::where('slug', $bosses[$bossIndex])->first();

            if ($update) {
                $accountBossCollection = $account->collection($collection)->first();

                // If account is missing the boss collection, create new
                if (!$accountBossCollection) {
                    $accountBossCollection = new $collection->model;
                }
            } else {
                $accountBossCollection = new $collection->model;
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
                $accountBossCollection->save();
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
        if ($update) {
            $dks = $account->collection(Collection::where('slug', 'dagannoth-kings')->first())->first();
        } else {
            $dks = new \App\Boss\DagannothKings;
        }

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
            $collection = Collection::findByNameAndCategory($npc, 4);

            if ($update) {
                $accountNpcCollection = $account->collection($collection)->first();

                // If account is missing the NPC collection, create new
                if (!$accountNpcCollection) {
                    $accountNpcCollection = new $collection->model;
                }
            } else {
                $accountNpcCollection = new $collection->model;
            }

            $accountNpcCollection->account_id = $account->id;

            try {
                $accountNpcCollection->save();
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        }

        return true;
    }

    public function loginLogout(Account $account, Request $request)
    {
        $account->online ^= 1;

        $account->save();

        $logData = [
            "user_id" => auth()->user()->id,
            "account_id" => $account->id,
            "category_id" => 8,
            "action" => $request->route()->getName(),
            "data" => $request->all() ?: null
        ];

        $log = Log::create($logData);

        $eventData = [
            "log_id" => $log->id,
            "type" => "event",
            "icon" => auth()->user()->icon_id,
            "message" => $account->username . " has logged " . ($account->online ? 'in' : 'out') . "!",
        ];

        $event = Broadcast::create($eventData);

        EventAll::dispatch($event);

        AccountOnline::dispatch($account);

        return response($account->username . " has been logged " . ($account->online ? 'in' : 'out') . " to RuneManager");
    }
}
