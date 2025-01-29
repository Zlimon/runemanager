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
    public function show(Account $account)
    {
        $bank = Bank::where([
            ['account_id', '=', $account->id],
            ['display', '=', 1]
        ])->first();

        if ($bank) {
            return response()->json($bank, 200);
        } else {
            return response("No bank for " . $account->username . " were found!", 404);
        }
    }

    public function update(Account $account, Request $request)
    {
        if (sizeof($request->all()) > 816) {
            return response("Not funny", 406);
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

        return response("Updated bank for " . $account->username, 200);
    }

    public function updateDisplay(Account $account)
    {
        $bank = Bank::where('account_id', $account->id)->first();
        if (!$bank) {
            return response("No bank for " . $account->username . " were found!", 404);
        }

        $bank->display ^= 1;

        $bank->save();

        AccountBank::dispatch($account);

        return response(($bank->display === 1 ? "Displaying" : "No longer displaying") . " bank for " . $account->username, 200);
    }
}
