<?php

namespace RuneManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuneManager\Helpers\Helper;
use RuneManager\Account;
use RuneManager\User;


class AdminAccountController extends Controller
{
    public function index() {
        $members = Account::with('user')->orderBy('created_at', 'DESC')->get();

        $query = null;

        return view('admin.member.index', compact('members', 'query'));
    }

    public function search() {
        request()->validate([
            'search' => ['required', 'string', 'min:1', 'max:13'],
        ]);

        $query = request('search');

        $members = Account::with('user')->where('username', 'LIKE', '%' . $query . '%')->paginate(10);

        if (count($members) === 0) {
            return redirect(route('admin-member'))->withErrors(['No search results for "'.$query.'"!']);
        } else {
            return view('admin.member.index', compact('members', 'query'));
        }
    }

    public function show($id) {
        $member = Account::findOrFail($id);

        return view('admin.member.show', compact('member'));
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
