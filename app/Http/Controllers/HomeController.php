<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();

        $accounts = $user->account;

        if ($accounts == null || count($accounts) <= 0) {
            return redirect(route('create-account'))->withErrors(['You must link an Old School RuneScape account to access this feature!']);
        } else {
            $skills = ["attack","defence","strength","hitpoints","ranged","prayer","magic","cooking","woodcutting","fletching","fishing","firemaking","crafting","smithing","mining","herblore","agility","thieving","slayer","farming","runecrafting","hunter","construction"];

            $stats = [];

            foreach ($accounts as $account) {

                foreach ($skills as $skillName) {
                    $stats[$account->username][] = DB::table($skillName)->where('account_id', $account->id)->get();
                }
            }

            return view('home', compact('user', 'accounts', 'stats', 'skills'));
        }
    }
}
