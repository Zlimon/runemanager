<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index() {
        $users = User::with('account')->orderByDesc('created_at')->get();

        $query = null;

        return view('admin.user.index', compact('users', 'query'));
    }

    public function show(User $user) {
        // TODO probably better ways to do this
        $account = [];
        foreach ($user->account as $userAccount) {
            $account[] = $userAccount::with('user')->where('id', $userAccount->id)->first();
        }

        return view('admin.user.show', compact('user', 'account'));
    }

    public function edit(User $user) {
        return view('admin.user.edit', compact('user'));
    }
}
