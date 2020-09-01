<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

use App\Helpers\Helper;
use App\Account;
use App\Collection;
use App\AccountAuthStatus;

use App\Boss\DagannothKings;

class AccountController extends Controller
{
    /**
     * Show all the application accounts.
     *
     * @return
     */
    public function index() {
        $accounts = Account::inRandomOrder()->get();

        $query = null;

        return view('account.index', compact('accounts', 'query'));
    }

    /**
     * Show the account creation page.
     *
     * @return
     */
    public function create() {
        if (Auth::check()) {
            if (Auth::user()->account->first()) {
            	return view('account.create');
                // TODO limit amount of account links setting
                //return redirect(route('home'))->withErrors('This profile has already been linked to a Old School RuneScape account!');
            } else {
                return view('account.create');
            }
        } else {
            return redirect(route('login'))->withErrors(['You have to log in before linking a Old School RuneScape account!']);
        }
    }

    /**
     * Verifies incoming account registration request.
     *
     * @return
     */
    public function createAccountAuthStatus() {
        if (Auth::check()) {
            request()->validate([
                'username' => ['required', 'string', 'min:1', 'max:13'],
            ]);

            if (Account::where('username', request('username'))->first()) {
                return redirect()->back()->withErrors('This account has already been linked to another profile!');
            } else {
                $authStatus = new AccountAuthStatus;

                $authStatus->user_id = Auth::user()->id;
                $authStatus->username = request('username');
                $authStatus->code = substr(md5(uniqid(mt_rand(), true)), 0, 8);
                $authStatus->status = "pending";

                $authStatus->save();

                return view('account.auth', compact('authStatus'));
            }
        } else {
            return redirect(route('login'))->withErrors(['You have to log in before linking a Old School RuneScape account!']);
        }
    }

    /**
     * Show a specific account and skills data from a URL request.
     *
     * @param  string  $username
     * @return
     */
    public function show($account) {
        $account = Account::findOrFail($account);

        return view('account.show', compact('account'));
    }

    /**
     * Returns search results from query.
     *
     * @return
     */
    public function search() {
        request()->validate([
            'search' => ['required', 'string', 'min:1', 'max:13'],
        ]);

        $query = request('search');

        $accounts = Account::with('user')->where('username', 'LIKE', '%' . $query . '%')->paginate(10);

        if (count($accounts) === 0) {
            return redirect(route('account'))->withErrors(['No search results for "'.$query.'"!']);
        } else {
            return view('account.index', compact('accounts', 'query'));
        }
    }
}
