<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Bank;
use App\Events\AccountBank;
use App\Http\Controllers\Controller;
use App\Log;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AccountBankController extends Controller
{
    public function show($accountUsername)
    {
        $account = Account::where('username', $accountUsername)->pluck('id')->first();

        if ($account) {
            $bank = Bank::where('account_id', $account)->first();

            if ($bank) {
                return response()->json($bank, 200);
            } else {
                return response()->json("No bank for " . $accountUsername . " were found!", 404);
            }
        }
    }

    public function update($accountUsername, Request $request)
    {
        $account = Account::where('user_id', auth()->user()->id)->where('username', $accountUsername)->first();
        if (!$account) {
            return response($accountUsername . " is not authenticated with " . auth()->user()->name, 401);
        }

        // Check if someone has tried something funny
        if (sizeof($request->all()) > 816) {
            return response($accountUsername . " is not authenticated with " . auth()->user()->name, 406);
        }

        Bank::updateOrInsert(
            ['account_id' => $account->id],
            [
                'data' => json_encode($request->all()),
                'created_at' => Carbon::now(), // TODO make better logic to not update this
                'updated_at' => Carbon::now(),
            ]
        );

        $bank = Bank::where('account_id', $account->id)->first();

        $logData = [
            "user_id" => auth()->user()->id,
            "account_id" => $account->id,
            "category_id" => 8,
            "description" => $request->route()->getName(),
            "data" => $request->all()
        ];

        $log = Log::create($logData);

        AccountBank::dispatch($account);

        return response("Updated bank for " . $accountUsername, 200);
    }
}
