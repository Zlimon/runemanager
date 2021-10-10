<?php

namespace App\Http\Controllers\Api;

use App\AccountAuthStatus;
use App\Helpers\Helper;
use App\Helpers\SettingHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AccountAuthController extends Controller
{
    public function store(Request $request)
    {
        if (AccountAuthStatus::whereUserId(Auth::user()->id)->where('status', '!=', 'success')->first()) {
            $errors = [
                'message' => 'Could not link account.',
                'errors' => [['You already have an account linked to this user waiting to be authenticated. Please complete the authentication process, or delete it to submit a new one.',]
                ],
            ];

            return response($errors, 500);
        }

        if (AccountAuthStatus::whereUserId(Auth::user()->id)->count() >= SettingHelper::getSetting('maximum_linked_accounts_per_user')) {
            $errors = [
                'message' => 'Could not link account.',
                'errors' => [['You have reached the maximum number of linked accounts allowed for this user.',]
                ],
            ];

            return response($errors, 500);
        }

        $request->validate([
            'account_username' => ['required', 'string', 'max:13', 'unique:account_auth_statuses,username', 'unique:accounts,username'],
            'account_type' => ['required', Rule::in(Helper::listAccountTypes())],
        ]);

        $playerDataUrl = Helper::formatHiscoreUrl($request->account_type, $request->account_username);

        if (!Helper::getPlayerData($playerDataUrl)) {
            $errors = [
                'message' => 'Could not link account.',
                'errors' => [
                    'account_username' => [
                        'Could not find this Old School RuneScape account. Did you pick correct account type?',
                    ]
                ],
            ];

            return response($errors, 500);
        }

        $accountAuthStatus = new AccountAuthStatus;

        $accountAuthStatus->user_id = Auth::id();
        $accountAuthStatus->account_type = $request->account_type;
        $accountAuthStatus->username = $request->account_username;
        $accountAuthStatus->code = substr(md5(uniqid(mt_rand(), true)), 0, 8);
        $accountAuthStatus->status = 'pending';

        $accountAuthStatus->save();

        return $accountAuthStatus;
    }

    public function updateAccountType(Request $request, AccountAuthStatus $accountAuthStatus)
    {
        if ($accountAuthStatus->account_type === $request->account_type) {
            return $accountAuthStatus;
        }

        $playerDataUrl = Helper::formatHiscoreUrl($request->account_type, $accountAuthStatus->username);

        if (!Helper::getPlayerData($playerDataUrl)) {
            $errors = [
                'message' => 'Could not update account type.',
                'errors' => [['Could not find an Old School RuneScape account as this type! Did you pick correct account type?',]
                ],
            ];

            return response($errors, 500);
        }

        $accountAuthStatus->account_type = $request->account_type;

        $accountAuthStatus->save();

        return $accountAuthStatus;
    }

    public function delete(Request $request, AccountAuthStatus $accountAuthStatus)
    {
        if ($accountAuthStatus->status === 'success') {
            return response('', 202);
        }

        $accountAuthStatus->delete();

        return response('', 202);
    }
}
