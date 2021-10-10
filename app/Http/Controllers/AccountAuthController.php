<?php

namespace App\Http\Controllers;

use App\AccountAuthStatus;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;

class AccountAuthController extends Controller
{
    public function index()
    {
        $accountTypes = Helper::listAccountTypes();

        $authStatus = AccountAuthStatus::whereUserId(Auth::user()->id)->where('status', '!=', 'success')->first();
        if ($authStatus) {
            return view('account.auth.index', compact('authStatus', 'accountTypes'));
        }

        return redirect(route('account-auth-create'));
    }

    public function create()
    {
        $accountTypes = Helper::listAccountTypes();

        $authStatus = AccountAuthStatus::whereUserId(Auth::user()->id)->where('status', '!=', 'success')->first();
        if ($authStatus) {
            return redirect(route('account-auth'));
        }

        return view('account.auth.create', compact('accountTypes'));
    }
}
