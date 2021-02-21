<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Equipment;
use App\Events\AccountEquipment;
use App\Http\Controllers\Controller;
use App\Log;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AccountEquipmentController extends Controller
{
    public function show($accountUsername)
    {
        $account = Account::where('username', $accountUsername)->pluck('id')->first();

        if ($account) {
            $equipment = Equipment::where('account_id', $account)->first();

            if ($equipment) {
                return response()->json($equipment, 200);
            } else {
                return response()->json("No equipment for " . $accountUsername . " were found!", 404);
            }
        }
    }

    public function update($accountUsername, Request $request)
    {
        $account = Account::where('user_id', auth()->user()->id)->where('username', $accountUsername)->first();
        if (!$account) {
            return response($accountUsername . " is not authenticated with " . auth()->user()->name, 401);
        }

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

        return response("Updated equipment for " . $accountUsername, 200);
    }
}
