<?php

namespace App\Http\Controllers;

use App\Account;
use App\AccountAuthStatus;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    /**
     * Show all the application accounts.
     *
     * @return
     */
    public function index()
    {
        $accounts = Account::inRandomOrder()->get();

        $query = null;

        return view('account.index', compact('accounts', 'query'));
    }

    /**
     * Show the account creation page.
     *
     * @return
     */
    public function create()
    {
        if (Auth::check()) {
            if (AccountAuthStatus::where('user_id', Auth::user()->id)->where('status', '!=', 'success')->first()) {
                // TODO limit amount of account links setting
                return redirect(route('show-account-auth'))->withErrors('You already have a pending status! You have to link this Old School RuneScape account to your RuneManager user before you can access this feature.');
            } else {
                return view('account.create');
            }
        } else {
            return redirect(route('login'))->withErrors(['You have to log in before linking a Old School RuneScape account!']);
        }
    }

    /**
     * Show a specific account and skills data from a URL request.
     *
     * @param string $username
     * @return
     */
    public function show($accountUsername)
    {
        $account = Account::where('username', $accountUsername)->firstOrFail();

        return view('account.show', compact('account'));
    }

    /**
     * Returns search results from query.
     *
     * @return
     */
    public function search()
    {
        request()->validate([
            'search' => ['required', 'string', 'min:1', 'max:13'],
        ]);

        $query = request('search');

        $accounts = Account::with('user')->where('username', 'LIKE', '%' . $query . '%')->paginate(10);

        if (count($accounts) === 0) {
            return redirect(route('account'))->withErrors(['No search results for "' . $query . '"!']);
        } else {
            return view('account.index', compact('accounts', 'query'));
        }
    }
}
