<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

use App\Helpers\Helper;
use App\Account;
use App\AccountAuthStatus;
use App\Collection;

use App\Http\Resources\AccountResource;

class AccountController extends Controller
{
    /**
     * Show all the application accounts.
     *
     * @return
     */
    public function index() {
        return AccountResource::collection(Account::with('user')->inRandomOrder()->get());
    }


    /**
     * Show a specific account and skills data from a URL request.
     *
     * @param  string  $username
     * @return
     */
    public function show($account) {
        return (new AccountResource(Account::findOrFail($account)));
    }

    /**
     * Create a new account instance after a valid registration.
     *
     * @param  string  $authCode
     * @return
     */
    public function store($authCode, Request $request) {
        $accountAuthStatus = AccountAuthStatus::where([
            ['username', $request->header('player')],
            ['status', 'pending']
        ])->first();

        if ($accountAuthStatus) {
            if ($request->header('type') == "NORMAL" || $request->header('type') == "IRONMAN" || $request->header('type') == "HARDCORE" || $request->header('type') == "ULTIMATE") {
                if ($authCode === $accountAuthStatus->code) {
                    $playerDataUrl = 'https://secure.runescape.com/m=hiscore_oldschool/index_lite.ws?player='.str_replace(' ', '%20', $request->header('player'));

                    /* Get the $playerDataUrl file content. */
                    $getPlayerData = file_get_contents($playerDataUrl);

                    /* Fetch the content from $playerDataUrl. */
                    $playerStats = explode("\n", $getPlayerData);

                    /* Convert the CSV file of player stats into an array */
                    $playerData = [];
                    foreach ($playerStats as $playerStat) {
                        $playerData[] = str_getcsv($playerStat);
                    }

                    $account = Account::create([
                        'user_id' => $accountAuthStatus->user_id,
                        'type' => strtolower($request->header('type')),
                        'username' => $request->header('player'),
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

                        $collectionLoot = new $collection->collection_type;

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

                    $accountAuthStatus->status = "success";

                    $accountAuthStatus->save();

                    return response()->json("Success", 200);
                    // return redirect(route('home'))->with('message', 'Old School RuneScape account "'.request('username').'" linked!');
                } else {
                    return response()->json("Invalid code", 401);
                }
            } else {
                return response()->json("Not a supported account type", 202);
            }
        } else {
            return response()->json("This account has no pending status", 202);
        }
    }
}
