<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\AccountAuthStatus;
use App\Collection;
use App\Events\AccountAll;
use App\Events\AccountOnline;
use App\Events\All;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\AccountBossResource;
use App\Http\Resources\AccountResource;
use App\Http\Resources\AccountSkillResource;
use App\Log;
use App\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    /**
     * Show a specific account and skills data from a URL request.
     *
     * @param string $username
     * @return
     */
    public function show($accountUsername)
    {
        return new AccountResource(Account::where('username', $accountUsername)->firstOrFail());
    }

    public function skill($accountUsername)
    {
        return new AccountSkillResource(Account::where('username', $accountUsername)->firstOrFail());
    }

    public function boss($accountUsername)
    {
        return new AccountBossResource(Account::where('username', $accountUsername)->firstOrFail());
    }

    /**
     * Create a new account instance after a valid registration.
     *
     * @param string $authCode
     * @return
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'min:1', 'max:13'],
            'code' => ['required', 'string', 'min:1', 'max:8'],
            'account_type' => ['required', Rule::in(Helper::listAccountTypes())],
        ]);

        if ($validator->fails()) {
            foreach ($validator->messages()->all() as $value) {
                return response($value, 406);
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
            return response("This account is registered as " . lcfirst(Helper::formatAccountTypeName($authStatus->account_type)) . ", not " . request('account_type'),
                    406);
        }

        if (request('code') !== $authStatus->code) {
            return response("Invalid code", 406);
        }

        $playerDataUrl = 'https://secure.runescape.com/m=hiscore_oldschool/index_lite.ws?player=' . str_replace(' ',
                '%20', $accountUsername);

        /* Get the $playerDataUrl file content. */
        $playerData = Helper::getPlayerData($playerDataUrl);

        if (!$playerData) {
            return response("Could not fetch player data from hiscores", 406);
        }

        $account = Account::create([
            'user_id' => $authStatus->user_id,
            'account_type' => request('account_type'),
            'username' => ucfirst($accountUsername),
            'rank' => $playerData[0][0],
            'level' => $playerData[0][1],
            'xp' => $playerData[0][2]
        ]);

        $skills = Helper::listSkills();

        for ($i = 0; $i < count($skills); $i++) {
            DB::table($skills[$i])->insert([
                'account_id' => $account->id,
                'rank' => ($playerData[$i + 1][0] >= 1 ? $playerData[$i + 1][0] : 0),
                'level' => $playerData[$i + 1][1],
                'xp' => ($playerData[$i + 1][2] >= 0 ? $playerData[$i + 1][2] : 0),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }

        $clueScrollAmount = count(Helper::listClueScrollTiers());

        $bosses = Helper::listBosses();

        array_splice($bosses, 13, 1);

        $bossIndex = 0;

        $dksKillCount = 0;

        for ($i = (count($skills) + $clueScrollAmount + 5); $i < (count($skills) + $clueScrollAmount + 5 + count($bosses)); $i++) {
            $collection = Collection::where('name', $bosses[$bossIndex])->firstOrFail();

            $collectionLog = new $collection->model;

            $collectionLog->account_id = $account->id;
            $collectionLog->kill_count = ($playerData[$i + 1][1] >= 0 ? $playerData[$i + 1][1] : 0);
            $collectionLog->rank = ($playerData[$i + 1][0] >= 0 ? $playerData[$i + 1][0] : 0);

            if (in_array($bosses[$bossIndex],
                ['dagannoth prime', 'dagannoth rex', 'dagannoth supreme'], true)) {
                $dksKillCount += ($playerData[$i + 1][1] >= 0 ? $playerData[$i + 1][1] : 0);
            }

            $collectionLog->save();

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

        $dks->save();

        $npcs = Helper::listNpcs();

        foreach ($npcs as $npc) {
            $collection = Collection::findByNameAndCategory($npc, 4);

            $collectionLog = new $collection->model;

            $collectionLog->account_id = $account->id;

            $collectionLog->save();
        }

        $authStatus->status = "success";

        $authStatus->save();

        return response("Account successfully authenticated!", 201);
    }

    public function loginLogout($accountUsername, Request $request)
    {
        $account = Account::where('user_id', auth()->user()->id)->where('username', $accountUsername)->first();

        if (!$account) {
            return response($accountUsername . " is not authenticated with " . auth()->user()->name, 401);
        }

        $account->online ^= 1;

        $account->save();

        $logData = [
            "user_id" => auth()->user()->id,
            "account_id" => $account->id,
            "category_id" => 8,
            "description" => $request->route()->getName(),
//            "data" => $data
        ];

        $log = Log::create($logData);

        $notificationData = [
            "log_id" => $log->id,
            "icon" => auth()->user()->icon_id,
            "message" => $accountUsername . " has logged " . ($account->online ? 'in' : 'out') . "!",
        ];

        $notification = Notification::create($notificationData);

        All::dispatch($notification);

        AccountOnline::dispatch($account);

        return response($accountUsername . " has been logged " . ($account->online ? 'in' : 'out') . " to RuneManager");
    }
}
