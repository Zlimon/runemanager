<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use Carbon\Carbon;

use App\Helpers\Helper;
use App\Account;
use App\AccountAuthStatus;
use App\Collection;

use App\Http\Resources\AccountResource;
use App\Http\Resources\AccountSkillResource;
use App\Http\Resources\AccountBossResource;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    /**
     * Show a specific account and skills data from a URL request.
     *
     * @param  string  $username
     * @return
     */
    public function show($accountUsername) {
        return new AccountResource(Account::where('username', $accountUsername)->firstOrFail());
    }

    public function skill($accountUsername) {
        return new AccountSkillResource(Account::where('username', $accountUsername)->firstOrFail());
    }

    public function boss($accountUsername) {
        return new AccountBossResource(Account::where('username', $accountUsername)->firstOrFail());
    }

    /**
     * Create a new account instance after a valid registration.
     *
     * @param  string  $authCode
     * @return
     */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'min:1', 'max:13'],
            'code' => ['required', 'string', 'min:1', 'max:8'],
            'account_type' => ['required', Rule::in(Helper::listAccountTypes())],
        ]);

        if ($validator->fails()) {
            foreach ($validator->messages()->all() as $value) {
                return response($value, 202);
            }
        }

        $authStatus = AccountAuthStatus::where('username', request('username'))->where('status', 'pending')->first();

        if ($authStatus) {
            if ($authStatus->user_id === auth()->user()->id) {
                if (request('account_type') === $authStatus->account_type) {
                    if (request('code') === $authStatus->code) {
                        $playerDataUrl = 'https://secure.runescape.com/m=hiscore_oldschool/index_lite.ws?player='.str_replace(' ', '%20', request('username'));

                        /* Get the $playerDataUrl file content. */
                        $getPlayerData = file_get_contents($playerDataUrl);

                        /* Fetch the content from $playerDataUrl. */
                        $playerStats = explode("\n", $getPlayerData);

                        /* Convert the CSV file of player stats into an array */
                        $playerData = [];
                        foreach ($playerStats as $playerStat) {
                            $playerData[] = str_getcsv($playerStat);
                        }

                        if ($playerData[0][0]) {
                            $account = Account::create([
                                'user_id' => $authStatus->user_id,
                                'account_type' => request('account_type'),
                                'username' => ucfirst(request('username')),
                                'rank' => $playerData[0][0],
                                'level' => $playerData[0][1],
                                'xp' => $playerData[0][2]
                            ]);

                            $skills = Helper::listSkills();

                            for ($i = 0; $i < count($skills); $i++) {
                                DB::table($skills[$i])->insert([
                                    'account_id' => $account->id,
                                    'rank' => ($playerData[$i+1][0] >= 1 ? $playerData[$i+1][0] : 0),
                                    'level' => $playerData[$i+1][1],
                                    'xp' => ($playerData[$i+1][2] >= 0 ? $playerData[$i+1][2] : 0),
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now()
                                ]);
                            }

                            $clueScrollAmount = count(Helper::listClueScrollTiers());

                            $bosses = Helper::listBosses();

                            array_splice($bosses, 13, 1);

                            $bossCounter = 0;

                            $dksKillCount = 0;

                            for ($i = (count($skills) + $clueScrollAmount + 4); $i < (count($skills) + $clueScrollAmount + 4 + count($bosses)); $i++) {
                                $collection = Collection::findByName($bosses[$bossCounter]);

                                $collectionLoot = new $collection->model;

                                $collectionLoot->account_id = $account->id;
                                $collectionLoot->kill_count = ($playerData[$i+1][1] >= 0 ? $playerData[$i+1][1] : 0);
                                $collectionLoot->rank = ($playerData[$i+1][0] >= 0 ? $playerData[$i+1][0] : 0);

                                if (in_array($bosses[$bossCounter], ['dagannoth prime', 'dagannoth rex', 'dagannoth supreme'], true)) {
                                    $dksKillCount += ($playerData[$i+1][1] >= 0 ? $playerData[$i+1][1] : 0);
                                }

                                $collectionLoot->save();

                                $bossCounter++;
                            }

                            /**
                             * Since there are no official total kill count hiscore for
                             * DKS' and we are going to retrieve loot for them from the
                             * collection log, we have to manually create a table.
                             * This might also happen with other bosses in the future
                             * that share collection log entry, but have seperate hiscores.
                             */
                            $dks = new \App\Boss\DagannothKings;

                            $dks->account_id = $account->id;
                            $dks->kill_count = $dksKillCount;

                            $dks->save();

                            $authStatus->status = "success";

                            $authStatus->save();

                            return response("Account successfully authenticated!", 201);
                        } else {
                            return response("Could not fetch player data from hiscores", 203);
                        }
                    } else {
                        return response("Invalid code", 202);
                    }
                } else {
                    return response("This account is registered as ".lcfirst(Helper::formatAccountTypeName($authStatus->account_type)).", not ".request('account_type'), 202);
                }
            } else {
                return response("This account is not linked to your user", 401);
            }
        } else {
            return response("This account has no pending status", 202);
        }
    }
}
