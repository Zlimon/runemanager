<?php

namespace RuneManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use RuneManager\Account;
use RuneManager\User;

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
     * Show the profile dashboard.
     *
     * @return \RuneManager\Account
     */
    public function index()
    {
        $member = Account::with('user')->where('user_id', Auth::user()->id)->first();

        if ($member == null) {
            return view('home')->withErrors(['You have not linked your RuneScape account with this profile!']);
        } else {
            $skills = ["attack","defence","strength","hitpoints","ranged","prayer","magic","cooking","woodcutting","fletching","fishing","firemaking","crafting","smithing","mining","herblore","agility","thieving","slayer","farming","runecrafting","hunter","construction"];

            $stats = [];

            foreach ($skills as $skillName) {
                array_push($stats, DB::table($skillName)->where('user_id', Auth::user()->id)->get());
            }

            return view('home', compact('member', 'stats', 'skills'));
        }
    }
}
