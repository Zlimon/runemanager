<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\AccountAuthStatus;
use App\Broadcast;
use App\Collection;
use App\Events\AccountOnline;
use App\Events\EventAll;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Log;
use App\Skill;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function authenticate(Request $request)
    {
        $request->validate([
            'account_username' => ['required', 'string', 'max:13'],
            'authentication_code' => ['required', 'string', 'min:8', 'max:8'],
            'account_type' => ['required', Rule::in(Helper::listAccountTypes())],
        ]);

        $accountUsername = $request->account_username;
        $accountType = $request->account_type;

        $authStatus = AccountAuthStatus::where('username', $accountUsername)->where('status', 'pending')->first();
        if (!$authStatus) {
            return response($accountUsername.' has no pending status.');
        }

        if ($authStatus->user_id !== Auth::id()) {
            return response($accountUsername.' is not linked to your user.', 403);
        }

        if ($accountType !== $authStatus->account_type) {
            return response('This account is registered as "'.lcfirst(Helper::formatAccountTypeName($authStatus->account_type)).'", not "'.$accountType.'".',406);
        }

        if ($request->authentication_code !== $authStatus->code) {
            return response('Invalid authentication code.', 406);
        }

        DB::beginTransaction();

        Helper::createOrUpdateAccount($accountUsername, $accountType, Auth::id());

        $authStatus->status = 'success';

        $authStatus->save();

        DB::commit();

        return response('Account '.$accountUsername.' successfully authenticated to user '.Auth::user()->name.'!', 201);
    }

    public function loginLogout(Account $account, Request $request)
    {
        $account->online ^= 1;

        $account->save();

        $logData = [
            "user_id" => auth()->user()->id,
            "account_id" => $account->id,
            "category_id" => 8,
            "action" => $request->route()->getName(),
            "data" => $request->all() ?: null
        ];

        $log = Log::create($logData);

        $eventData = [
            "log_id" => $log->id,
            "type" => "event",
            "icon" => auth()->user()->icon_id,
            "message" => $account->username." has logged ".($account->online ? 'in' : 'out')."!",
        ];

        $event = Broadcast::create($eventData);

        EventAll::dispatch($event);

        AccountOnline::dispatch($account);

        return response($account->username." has been logged ".($account->online ? 'in' : 'out')." to RuneManager");
    }
}
