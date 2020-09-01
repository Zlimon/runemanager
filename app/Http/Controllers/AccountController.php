<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

use App\Helpers\Helper;
use App\Account;
use App\Collection;

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
    public function verifyAccount() {
        if (Auth::check()) {
            request()->validate([
                'username' => ['required', 'string', 'min:1', 'max:13'],
            ]);

            if (Account::where('username', request('username'))->first()) {
                return redirect()->back()->withErrors('This account has already been linked to another profile!');
            } else {
                // Verify account mode
                if (request('mode') == "normal") {
                    $playerDataUrl = 'https://secure.runescape.com/m=hiscore_oldschool/index_lite.ws?player='.str_replace(' ', '%20', request('username'));
                } elseif (request('mode') == "ironman" || request('mode') == "hardcore" || request('mode') == "ultimate") {
                    $playerDataUrl = 'https://secure.runescape.com/m=hiscore_oldschool_'.request('mode').'/index_lite.ws?player='.str_replace(' ', '%20', request('username'));
                } else {
                    $playerDataUrl = 'https://secure.runescape.com/m=hiscore_oldschool/index_lite.ws?player='.str_replace(' ', '%20', request('username'));
                }

                if (Helper::verifyUrl($playerDataUrl)) {
                    $playerDataUrl = 'https://secure.runescape.com/m=hiscore_oldschool/index_lite.ws?player='.str_replace(' ', '%20', request('username'));

                    /* Get the $playerDataUrl file content. */
                    $getPlayerData = file_get_contents($playerDataUrl);

                    /* Fetch the content from $playerDataUrl. */
                    $playerStats = explode("\n", $getPlayerData);

                    /* Convert the CSV file of player stats into an array */
                    $playerData = [];
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
                'mode' => request('mode'),
                'username' => request('username'),
                'rank' => $playerData[0][0],
                'level' => $playerData[0][1],
                'xp' => $playerData[0][2]
            ]);

            $skills = Helper::listSkills();

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

            $clueScrollAmount = count(Helper::listClueScrollTiers());

            $bosses = Helper::listBosses();

            $bossCounter = 0;

            $dksKillCount = 0;

            for ($i = (count($skills) + $clueScrollAmount + 4); $i < (count($skills) + $clueScrollAmount + 4 + count($bosses)); $i++) {
                $collection = Collection::findByName($bosses[$bossCounter]);

                $collectionLoot = new $collection->collection_type;

                $collectionLoot->account_id = $account->id;
                $collectionLoot->kill_count = ($playerData[$i+1][1] >= 0 ? $playerData[$i+1][1] : 0);
                $collectionLoot->rank = ($playerData[$i+1][0] >= 0 ? $playerData[$i+1][0] : 0);

                if (in_array($bosses[$bossCounter], ['dagannoth prime', 'dagannoth rex', 'dagannoth supreme'], true)) {
                    $dksKillCount += ($playerData[$i+1][1] >= 0 ? $playerData[$i+1][1] : 0);
                }

                $collectionLoot->save();

                $bossCounter++;
            }

            /**
             * Since there are no total kill count hiscore for DKS
             * and we are going to retrieve loot for them from the
             * collection log, we have to manually create a row.
             * This might also happen with other bosses in the future.
             */
            $collectionLoot = new \App\Boss\DagannothKings;

            $collectionLoot->account_id = $account->id;
            $collectionLoot->kill_count = $dksKillCount;

            $collectionLoot->save();

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
