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

    public function search() {
        request()->validate([
            'search' => ['required', 'string', 'min:1', 'max:13'],
        ]);

        $query = request('search');

        $accounts = Account::with('user')->where('username', 'LIKE', '%' . $query . '%')->get();

        if (count($accounts) === 0) {
            return redirect(route('admin-account'))->withErrors(['No search results for "'.$query.'"!']);
        } else {
            return view('admin.account.index', compact('accounts', 'query'));
        }
    }

    public function show(Account $account) {
        $account = $account::with('user')->first();

        return view('admin.account.show', compact('account'));
    }

    public function create() {
        return view('admin.account.create');
    }

    public function store(Request $request) {
        $request->validate([
            'account' => ['required', 'string', 'min:1', 'max:13'],
            'user' => ['max:255'],
        ]);

        if (Account::where('username', request('account'))->first()) {
            return redirect(route('admin-create-account'))->withErrors('This account has already been registered!');
        }

        if (!request('user') && Auth::check()) {
            $user = Auth::getUser();
        } elseif (request('user')) {
            $user = User::whereName(request('user'))->orWhere('id', request('user'))->first();

            if (!$user) {
                return redirect(route('admin-create-account'))->withErrors('This user does not exist!');
            }
        } else {
            return redirect(route('admin-create-account'))->withErrors('This user does not exist!');
        }

        $playerDataUrl = 'https://secure.runescape.com/m=hiscore_oldschool/index_lite.ws?player=' . str_replace(
                ' ',
                '%20',
                request('account')
            );

        /* Get the $playerDataUrl file content. */
        $playerData = Helper::getPlayerData($playerDataUrl);

        if (!$playerData) {
            return redirect(route('admin-create-account'))->withErrors('Could not fetch player data from hiscores!');
        }

        DB::beginTransaction();

        try {
            $account = Account::create(
                [
                    'user_id' => $user->id,
                    'account_type' => 'normal',
                    'username' => request('account'),
                    'rank' => $playerData[0][0],
                    'level' => $playerData[0][1],
                    'xp' => $playerData[0][2]
                ]
            );
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        try {
            Helper::createOrUpdateAccountHiscores($account, $playerData);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        DB::commit();

        return redirect(route('admin-show-account', $account))->with('message', 'Old School RuneScape account "'.request('account').'" registered to user "'.$user->name.'"!');
    }

    public function update(Account $account, Request $request) {
        $newOwner = User::whereName(request('user'))->orWhere('id', request('user'))->first();

        if (!$newOwner) {
            return redirect(route('admin-show-account', $account))->withErrors(['This user does not exist!']);
        }

        $account->update([
            'user_id' => $newOwner->id
        ]);

        return redirect(route('admin-show-account', $account))->with('message', 'Account ownership transferred to "'.$newOwner->name.'"!');
    }
}
