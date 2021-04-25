<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Events\AccountQuest;
use App\Helpers\Helper;
use App\Quest;
use App\Events\AccountEquipment;
use App\Http\Controllers\Controller;
use App\Log;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AccountQuestController extends Controller
{
    public function show(Account $account)
    {
        if ($account) {
            $quests = Quest::where([
                ['account_id', '=', $account->id],
                ['display', '=', 1]
            ])->first();

            if ($quests) {
                return response()->json($quests, 200);
            } else {
                return response("No quests for " . $account->username . " were found!", 404);
            }
        }
    }

    public function update(Account $account, Request $request)
    {
        Quest::updateOrInsert(
            ['account_id' => $account->id],
            [
                'data' => json_encode($request->all()),
                'created_at' => Carbon::now(), // TODO make better logic to not update this
                'updated_at' => Carbon::now(),
            ]
        );

        $quests = Quest::where('account_id', $account->id)->first();

        $logData = [
            "user_id" => auth()->user()->id,
            "account_id" => $account->id,
            "category_id" => 8,
            "action" => $request->route()->getName(),
            "data" => $request->all()
        ];

        $log = Log::create($logData);

        AccountQuest::dispatch($quests);

        return response("Updated quest list for " . $account->username, 200);
    }

    public function updateDisplay(Account $account)
    {
        $quests = Quest::where('account_id', $account->id)->first();
        if (!$quests) {
            return response("No quests for " . $account->username . " were found!", 404);
        }

        $quests->display ^= 1;

        $quests->save();

        AccountQuest::dispatch($quests);

        return response(($quests->display === 1 ? "Displaying" : "No longer displaying") . " quest journals for " . $account->username, 200);
    }
}
