<?php

namespace App\Http\Controllers;

use App\Account;
use App\AccountAuthStatus;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AccountAuthController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $authStatus = AccountAuthStatus::with('user')->where('user_id', Auth::user()->id)->where('status', '!=',
                'success')->first();

            if ($authStatus) {
                return view('account.auth', compact('authStatus'));
            } else {
                return redirect(route('account-create'))->withErrors(['You should register a Old School RuneScape account first!']);
            }
        } else {
            return redirect(route('login'))->withErrors(['You have to log in before linking a Old School RuneScape account!']);
        }
    }

    public function updateAccountType()
    {
        $authStatus = AccountAuthStatus::where('user_id', Auth::user()->id)->where('status', '!=', 'success')->first();

        if ($authStatus) {
            if ($authStatus->account_type != request('account_type')) {
                $playerDataUrl = Helper::formatHiscoreUrl(request('account_type'), $authStatus->username);

                if (Helper::getPlayerData($playerDataUrl)) {
                    $authStatus->account_type = request('account_type');

                    $authStatus->save();

                    return redirect(route('account-auth'))->with('message', 'Account type updated!');
                } else {
                    return redirect()->back()->withErrors('Could not find this Old School RuneScape account! Did you pick correct account type?');
                }
            } else {
                return redirect()->back()->withErrors('This account is already registered as ' . Helper::formatAccountTypeName(request('account_type')) . '!');
            }
        } else {
            return redirect(route('account-create'))->withErrors(['This account does not have a pending status anymore!']);
        }
    }

    public function delete()
    {
        $authStatus = AccountAuthStatus::where('user_id', Auth::user()->id)->where('status', '!=', 'success')->first();

        if ($authStatus) {
            $authStatus->delete();

            return redirect(route('account-create'))->with('message', 'Account authentication status deleted!');
        } else {
            return redirect(route('account-create'))->withErrors(['This account does not have a pending status anymore!']);
        }
    }
}
