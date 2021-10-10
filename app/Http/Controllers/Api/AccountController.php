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
            $errors = [
                'message' => 'Could not authenticate account.',
                'errors' => [['"'.$accountUsername.'" has no pending status.'],
                ],
            ];

            return response($errors, 404);
        }

        if ($authStatus->user_id !== Auth::id()) {
            $errors = [
                'message' => 'Could not authenticate account.',
                'errors' => [['"'.$accountUsername.'" is not linked to user "'.Auth::user()->name.'"."'],
                ],
            ];

            return response($errors, 403);
        }

        if ($accountType !== $authStatus->account_type) {
            $errors = [
                'message' => 'Could not authenticate account.',
                'errors' => [['"'.$accountUsername.'" is registered as a "'.ucwords(Helper::formatAccountTypeName($authStatus->account_type)).'" account, not "'.ucwords(Helper::formatAccountTypeName($accountType)).'". Account type can be updated on the authentication page.'],
                ],
            ];

            return response($errors, 422);
        }

        if ($request->authentication_code !== $authStatus->code) {
            $errors = [
                'message' => 'Could not authenticate account.',
                'errors' => [['Invalid authentication code.'],
                ],
            ];

            return response($errors, 406);
        }

        DB::beginTransaction();

        $account = Helper::createOrUpdateAccount($accountUsername, $accountType, Auth::id());

        if (!$account instanceof Account) {
            $errors = [
                'message' => 'Could not authenticate account.',
                'errors' => [[$account],
                ],
            ];

            return response($errors, 500);
        }

        $authStatus->status = 'success';

        $authStatus->save();

        DB::commit();

        $success = [
            'message' => 'Successfully authenticated account to user.',
            'errors' => [['Account "'.$accountUsername.'" successfully authenticated to user "'.Auth::user()->name.'".'],
            ],
        ];

        return response($success, 201);
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
