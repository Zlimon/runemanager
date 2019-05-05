<?php

namespace RuneManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use RuneManager\Account;
use RuneManager\User;
use RuneManager\Helpers\Helper;
use Carbon\Carbon;

class AccountsController extends Controller
{

    /**
     * Show all the application members.
     *
     * @return
     */
    public function index() {
        $members = Account::with('user')->inRandomOrder()->get();

        return view('member.index', compact('members'));
    }

    /**
     * Show the member creation page.
     *
     * @return
     */
    public function create() {
        if (Auth::check()) {
            if (Auth::user()->member->first()) {
                return redirect()->back()->withErrors('This profile has already been linked to a Old School RuneScape account!');
            } else {
                return view('member.create');
            }
        } else {
            return redirect(route('login'))->withErrors(['You have to log in before linking a Old School RuneScape account!']);
        }
    }

    /**
     * Verifies incoming member registration request.
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
     * Create a new member instance after a valid registration.
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
                    'rank' => $playerData[$i+1][0],
                    'level' => $playerData[$i+1][1],
                    'xp' => $playerData[$i+1][2],
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
     * Show a specific member and skills data from a URL request.
     *
     * @param  string  $username
     * @return
     */
    public function show($member) {
        $member = Account::findOrFail($member);

        $skills = ["attack","defence","strength","hitpoints","ranged","prayer","magic","cooking","woodcutting","fletching","fishing","firemaking","crafting","smithing","mining","herblore","agility","thieving","slayer","farming","runecrafting","hunter","construction"];

        $stats = [];

        foreach ($skills as $skillName) {
            array_push($stats, DB::table($skillName)->where('account_id', $member->id)->get());
        }

        return view('member.show', compact('member', 'stats', 'skills'));
    }
}
