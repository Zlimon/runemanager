<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

use App\Helpers\Helper;
use App\Account;

class AccountController extends Controller
{
    /**
     * Show all the application accounts.
     *
     * @return
     */
    public function index() {
        $accounts = Account::with('user')->inRandomOrder()->get();

        return view('account.index', compact('accounts'));
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
    public function verifyAccount() {
        if (Auth::check()) {
            request()->validate([
                'username' => ['required', 'string', 'min:1', 'max:13'],
            ]);

            if (Account::where('username', request('username'))->first()) {
                return redirect()->back()->withErrors('This account has already been linked to another profile!');
            } else {
                $playerDataUrl = 'https://secure.runescape.com/m=hiscore_oldschool/index_lite.ws?player='.request('username');

                if (Helper::verifyUrl($playerDataUrl)) {
                    /* Get the $playerDataUrl file content. */
                    $getPlayerData = file_get_contents($playerDataUrl);

                    /* Fetch the content from $playerDataUrl. */
                    $playerStats = explode("\n", $getPlayerData);

                    /* Convert the CSV file of player stats into an array */
                    $playerData = array();
                    foreach ($playerStats as $playerStat) {
                        $playerData[] = str_getcsv($playerStat);
                    }

                    return $this->store($playerData);
                } else {
                    return redirect()->back()->withErrors('Could not find this Old School RuneScape account!');
                }
            }
        } else {
            return redirect(route('login'))->withErrors(['You have to log in before linking a Old School RuneScape account!']);
        }
    }

    /**
     * Create a new account instance after a valid registration.
     *
     * @param  array  $playerData
     * @return
     */
    public function store(Array $playerData) {
        if (Auth::check()) {
            $account = Account::create([
                'user_id' => Auth::user()->id,
                'username' => request('username'),
                'rank' => $playerData[0][0],
                'level' => $playerData[0][1],
                'xp' => $playerData[0][2]
            ]);

            $skills = ["attack","defence","strength","hitpoints","ranged","prayer","magic","cooking","woodcutting","fletching","fishing","firemaking","crafting","smithing","mining","herblore","agility","thieving","slayer","farming","runecrafting","hunter","construction"];

            for ($i = 0; $i < count($skills); $i++) {
                DB::table($skills[$i])->insert([
                    'account_id' => $account->id,
                    'rank' => ($playerData[$i+1][0] >= 1 ? $playerData[$i+1][0] : 0),
                    'level' => $playerData[$i+1][1],
                    'xp' => ($playerData[$i+1][2] >= 0 ? $playerData[$i+1][2] : 0),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }

            return redirect(route('home'))->with('message', 'Old School RuneScape account "'.request('username').'" linked!');
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

        $skills = ["attack","defence","strength","hitpoints","ranged","prayer","magic","cooking","woodcutting","fletching","fishing","firemaking","crafting","smithing","mining","herblore","agility","thieving","slayer","farming","runecrafting","hunter","construction"];

        $stats = [];

        foreach ($skills as $skillName) {
            array_push($stats, DB::table($skillName)->where('account_id', $account->id)->get());
        }

        return view('account.show', compact('account', 'stats', 'skills'));
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

        $searchResults = Account::with('user')->where('username', 'LIKE', '%' . $query . '%')->paginate(10);

        if (count($searchResults) === 0) {
            return redirect(route('account'))->withErrors(['No search results for "'.$query.'"!']);
        } else {
            return view('account.search', compact('searchResults', 'query'));
        }
    }
}
