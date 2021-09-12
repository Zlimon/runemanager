<?php

namespace App\Http\Controllers;

use App\Account;
use App\AccountAuthStatus;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    /**
     * Show all the application accounts.
     *
     * @return
     */
    public function index()
    {
        $accounts = Account::orderByDesc('level')->orderByDesc('xp')->get();

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
                return redirect(route('account-auth-show'))->withErrors('You already have a pending status! You have to link this Old School RuneScape account to your RuneManager user before you can access this feature.');
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
    public function show(Account $account)
    {
        $account = $account::with('user')->first();

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
            'search' => ['nullable', 'string', 'max:13'],
            'account_type' => ['nullable', Rule::in(Helper::listAccountTypes())],
            'order_by' => [Rule::in(['level', 'xp', 'rank', 'account_type', 'user_id'])],
            'order_by_order' => [Rule::in(['asc', 'desc'])],
            'total_level_between_from' => ['integer'],
            'total_level_between_to' => ['integer'],
        ]);

        $query = request('search');

        $accounts = Account::with('user')
            ->where('username', 'LIKE', '%' . $query . '%')
            ->when(in_array(request('account_type'),
                Helper::listAccountTypes()), function ($query, $accountType) {
                return $query->where('account_type', request('account_type'));
            })
            ->whereBetween('level', [request('total_level_between_from'), request('total_level_between_to')])//
            ->orderBy(request('order_by'), request('order_by_order'))
            ->get();

        if (count($accounts) === 0) {
            return redirect(route('account'))->withErrors(['No search results for "' . $query . '"!']);
        } else {
            return view('account.index', compact('accounts', 'query'));
        }
    }
}
