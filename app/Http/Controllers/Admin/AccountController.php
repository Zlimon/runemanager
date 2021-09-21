<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index() {
        $accounts = Account::with('user')->orderByDesc('created_at')->get();

        $query = null;

        return view('admin.account.index', compact('accounts', 'query'));
    }

    public function show(Account $account) {
        $account = $account::with('user')->where('id', $account->id)->first();

        return view('admin.account.show', compact('account'));
    }

    public function create() {
        return view('admin.account.create');
    }
}
