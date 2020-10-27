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

    /**
     * Create a new account instance after a valid registration.
     *
     * @param  string  $authCode
     * @return
     */
    public function store($accountUsername) {
        if (in_array(request('account_type'), Helper::listAccountTypes(), true)) {
            $authStatus = AccountAuthStatus::where('username', $accountUsername)->where('status', 'pending')->first();

            if ($authStatus) {
                if (request('account_type') === $authStatus->account_type) {
                    if (request('code') === $authStatus->code) {
                        $playerDataUrl = 'https://secure.runescape.com/m=hiscore_oldschool/index_lite.ws?player='.str_replace(' ', '%20', $accountUsername);

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
                                'username' => $accountUsername,
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
                             * This might also happen with other bosses in the future.
                             */
                            $collectionLoot = new \App\Boss\DagannothKings;

                            $collectionLoot->account_id = $account->id;
                            $collectionLoot->kill_count = $dksKillCount;

                            $collectionLoot->save();

                            $authStatus->status = "success";

                            $authStatus->save();

                            return response()->json("Account successfully authenticated!", 200);
                        } else {
                            return response()->json("Could not fetch player data from hiscores", 202);
                        }
                    } else {
                        return response()->json("Invalid code", 202);
                    }
                } else {
                    return response()->json("This account is registered as ".Helper::formatAccountTypeName($authStatus->account_type).", not ".request('account_type'), 202);
                }
            } else {
                return response()->json("This account has no pending status", 202);
            }
        } else {
            return response()->json("Not a supported account type. Valid account types: ".implode(", ", str_replace('_', ' ', Helper::listAccountTypes())), 202);
        }
    }
}
