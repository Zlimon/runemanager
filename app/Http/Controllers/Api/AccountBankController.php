<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Bank;
use App\Events\AccountBank;
use App\Helpers\Helper;
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
            $bank = Bank::where([
                ['account_id', '=', $account],
                ['display', '=', 1]
            ])->first();

            if ($bank) {
                return response()->json($bank, 200);
            } else {
                return response("No bank for " . $accountUsername . " were found!", 404);
            }
        }
    }

    public function update($accountUsername, Request $request)
    {
        $account = Helper::checkIfUserOwnsAccount($accountUsername);

        // Check if someone has tried something funny
        if (sizeof($request->all()) > 816) {
            return response($accountUsername . " is not authenticated with " . auth()->user()->name, 406);
        }

        $totalBankValue = 0;

        $priceType = "gePrice"; // TODO gePrice / haPrice -> Admin panel

        foreach ($request->all() as $bankItem) {
            $totalBankValue += $bankItem[$priceType] * $bankItem["quantity"];
        }

        Bank::updateOrInsert(
            ['account_id' => $account->id],
            [
                'data' => json_encode($request->all()),
                'total' => $totalBankValue,
                'created_at' => Carbon::now(), // TODO make better logic to not update this
                'updated_at' => Carbon::now(),
            ]
        );

        $bank = Bank::where('account_id', $account->id)->first();

        $logData = [
            "user_id" => auth()->user()->id,
            "account_id" => $account->id,
            "category_id" => 8,
            "action" => $request->route()->getName(),
            "data" => $request->all(),
            "total" => $totalBankValue
        ];

        $log = Log::create($logData);

        AccountBank::dispatch($account);

        return response("Updated bank for " . $accountUsername, 200);
    }

    public function updateDisplay($accountUsername)
    {
        $account = Helper::checkIfUserOwnsAccount($accountUsername);

        $bank = Bank::where('account_id', $account->id)->first();

        if (!$bank) {
            return response("No bank for " . $accountUsername . " were found!", 404);
        }

        $bank->display ^= 1;

        $bank->save();

        AccountBank::dispatch($account);

        return response(($bank->display === 1 ? "Displaying" : "No longer displaying") . " bank for " . $accountUsername, 200);
    }
}
