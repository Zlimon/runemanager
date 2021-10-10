<?php

namespace App\Http\Controllers;

use App\Account;
use App\Helpers\Helper;
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

    /**
     * Show the account comparison page
     *
     * @return
     */
    public function compare()
    {
        return view('account.compare');
    }
}
