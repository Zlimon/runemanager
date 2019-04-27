<?php

namespace OSRSCM\Http\Controllers;

use Illuminate\Http\Request;
use OSRSCM\Member;

class PagesController extends Controller
{
    /**
     * Show the application hiscores.
     * @return \OSRSCM\Member
     */
    public function hiscore() {
        $castXpAsInt = "CAST(xp AS INT)";

        $members = Member::orderBy('rank', 'ASC')->orderBy('level', 'DESC')->orderByRaw($castXpAsInt, 'DESC')->get();

        return view('hiscore', compact('members'));
    }

    /**
     * Show the latest member updates.
     * @return \OSRSCM\Member
     */
    public function updateLog() {
        $updates = Member::orderBy('updated_at', 'DESC')->whereColumn('updated_at', '>', 'created_at')->get();

        return view('update-log', compact('updates'));
    }
}
