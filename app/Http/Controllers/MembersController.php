<?php

namespace OSRSCM\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OSRSCM\Member;

class MembersController extends Controller
{
    /**
     * Show all the application members.
     * @return \OSRSCM\Member
     */
    public function index() {
        $members = Member::inRandomOrder()->get();

        return view('member.index', compact('members'));
    }

    /**
     * Show a specific member from a URL request.
     *
     * @param  string  $username
     * @return \OSRSCM\Member
     */
    public function show($username) {
        $member = Member::where('username', $username)->first();

        $skills = ["attack","defence","strength","hitpoints","ranged","prayer","magic","cooking","woodcutting","fletching","fishing","firemaking","crafting","smithing","mining","herblore","agility","thieving","slayer","farming","runecrafting","hunter","construction"];

        $stats = [];

        foreach ($skills as $skillName) {
            array_push($stats, DB::table($skillName)->where('username', $username)->get());
        }

        return view('member.show', compact('member', 'stats', 'skills'));
    }
}
