<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\AccountAuthStatus;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AccountAuthController extends Controller
{
    public function create(Request $request)
    {
//        if (Auth::id() === $authStatus->user_id) {
//            return view('account.auth', compact('authStatus'));
//        }

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
                        'Could not find this Old School RuneScape account! Did you pick correct account type?',
                    ]
                ],
            ];

            return response($errors, 500);
        }

        $authStatus = new AccountAuthStatus;

        $authStatus->user_id = Auth::id();
        $authStatus->account_type = $request->account_type;
        $authStatus->username = $request->account_username;
        $authStatus->code = substr(md5(uniqid(mt_rand(), true)), 0, 8);
        $authStatus->status = 'pending';

        $authStatus->save();

        return view('account.auth', compact('authStatus'));
    }
}
