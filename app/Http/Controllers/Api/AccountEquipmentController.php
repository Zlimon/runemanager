<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Equipment;
use App\Events\AccountEquipment;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Log;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AccountEquipmentController extends Controller
{
    public function show($accountUsername)
    {
        $account = Helper::getAccountIdFromUsername($accountUsername);

        if ($account) {
            $equipment = Equipment::where([
                ['account_id', '=', $account],
                ['display', '=', 1]
            ])->first();

            if ($equipment) {
                return response()->json($equipment, 200);
            } else {
                return response("No equipment for " . $accountUsername . " were found!", 404);
            }
        }
    }

    public function update(Account $account, Request $request)
    {
        Equipment::updateOrInsert(
            ['account_id' => $account->id],
            [
                'data' => json_encode($request->all()),
                'created_at' => Carbon::now(), // TODO make better logic to not update this
                'updated_at' => Carbon::now(),
            ]
        );

        $equipment = Equipment::where('account_id', $account->id)->first();

        $logData = [
            "user_id" => auth()->user()->id,
            "account_id" => $account->id,
            "category_id" => 8,
            "action" => $request->route()->getName(),
            "data" => $request->all()
        ];

        $log = Log::create($logData);

        AccountEquipment::dispatch($equipment);

        return response("Updated equipment for " . $account->username, 200);
    }

    public function updateDisplay(Account $account)
    {
        $equipment = Equipment::where('account_id', $account->id)->first();
        if (!$equipment) {
            return response("No equipment for " . $account->username . " were found!", 404);
        }

        $equipment->display ^= 1;

        $equipment->save();

        AccountEquipment::dispatch($equipment);

        return response(($equipment->display === 1 ? "Displaying" : "No longer displaying") . " equipment for " . $account->username, 200);
    }
}
