<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index() {
        $accounts = Account::with('user')->orderByDesc('created_at')->get();

        $query = null;

        return view('admin.member.index', compact('accounts', 'query'));
    }

    public function search() {
        request()->validate([
            'search' => ['required', 'string', 'min:1', 'max:13'],
        ]);

        $query = request('search');

        $accounts = Account::with('user')->where('username', 'LIKE', '%' . $query . '%')->get();

        if (count($accounts) === 0) {
            return redirect(route('admin-member'))->withErrors(['No search results for "'.$query.'"!']);
        } else {
            return view('admin.member.index', compact('accounts', 'query'));
        }
    }

    public function show(Account $account) {
        return view('admin.member.show', compact('account'));
    }

    public function create() {
        return view('admin.member.create');
    }

    public function store(Request $request) {
        $request->validate([
            'username' => ['required', 'string', 'min:1', 'max:13'],
        ]);

        if (Account::where('username', request('username'))->first()) {
            return redirect(route('admin-create-member'))->withErrors('This account has already been registered!');
        } else {
            $account = Helper::registerAccount(request('username'));

            if ($account) {
                return redirect(route('admin-show-member', $account))->with('message', 'Old School RuneScape account "'.request('username').'" registered!');
            } else {
                return redirect(route('admin-create-member'))->withErrors('Could not find this Old School RuneScape account!');
            }
        }
    }

    public function update(Account $id) {
        $newOwner = User::where('name', request('user_id'))->first();

        if ($newOwner) {
            $id->update([
                'user_id' => $newOwner->id
            ]);

            return redirect(route('admin-show-member', $id))->with('message', 'Account ownership transferred to "'.$newOwner->name.'"!');
        } else {
            $newOwner = User::find(request('user_id'));

            if ($newOwner) {
                $id->update([
                    'user_id' => $newOwner->id
                ]);

                return redirect(route('admin-show-member', $id))->with('message', 'Account ownership transferred to "'.$newOwner->name.'"!');
            } else {
                return redirect(route('admin-show-member', $id))->withErrors(['This user does not exist!']);
            }
        }
    }
}
