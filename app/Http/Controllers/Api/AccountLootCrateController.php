<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Collection;
use App\Events\AccountAll;
use App\Events\AccountKill;
use App\Events\All;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Log;
use App\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AccountLootCrateController extends Controller
{
    public function store($accountUsername, Request $request)
    {
        $account = Account::where('user_id', auth()->user()->id)->where('username', $accountUsername)->first();

        if ($account) {
            $loot = $request->except([
                "icon_id",
                "crate_type",
                "total_value",
            ]);

            $dataJson = '{"collection":' . json_encode($loot) . ',"loot":' . json_encode($loot) . '}';

            $data = json_decode($dataJson, true);

            foreach ($loot as $key => $item) {
                Helper::downloadItemIcon($key);
            }

            $logData = [
                "user_id" => auth()->user()->id,
                "account_id" => $account->id,
                "category_id" => 9,
                "data" => $data
            ];

            $log = Log::create($logData);

            $notificationData = [
                "log_id" => $log->id,
                "icon" => $request["icon_id"],
                "message" => $accountUsername . " just opened a " . $request["crate_type"] . "!",
            ];

            $notification = Notification::create($notificationData);

            All::dispatch($notification);

            AccountAll::dispatch($account, $notification);
        }
    }
}
