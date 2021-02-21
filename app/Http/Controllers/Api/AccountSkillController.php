<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Events\AccountAll;
use App\Events\AccountLevelUp;
use App\Events\All;
use App\Http\Controllers\Controller;
use App\Log;
use App\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountSkillController extends Controller
{
    public function update($accountUsername, $skillName, Request $request)
    {
        $account = Account::where('user_id', auth()->user()->id)->where('username', $accountUsername)->first();
        if (!$account) {
            return response($accountUsername . " is not authenticated with " . auth()->user()->name, 401);
        }

        DB::table($skillName)->where('account_id', $account->id)->update(['level' => $request->level]);

        $skill = DB::table($skillName)->where('account_id', $account->id)->first();

        $logData = [
            "user_id" => auth()->user()->id,
            "account_id" => $account->id,
            "category_id" => 1,
            "action" => $request->route()->getName(),
            "data" => $request->all()
        ];

        $log = Log::create($logData);

        $notificationData = [
            "log_id" => $log->id,
            "icon" => strtolower($skillName),
            "message" => $accountUsername . " just achieved level " . $skill->level . " " . ucfirst($skillName) . "!" . ($skill->level === 92 ? " Half way there!" : ""),
        ];

        $notification = Notification::create($notificationData);

        All::dispatch($notification);

        AccountAll::dispatch($account, $notification);

        AccountLevelUp::dispatch($account, $notification);

        return response("Advanced " . ucfirst($skillName) . " level for " . $accountUsername . " to level " . $request->level, 200);
    }
}
