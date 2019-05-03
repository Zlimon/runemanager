<?php

namespace RuneManager\Http\Controllers;

use Illuminate\Http\Request;
use RuneManager\Account;

class PagesController extends Controller
{
    /**
     * Show the application hiscores.
     *
     * @return
     */
    public function hiscore() {
        $castXpAsInt = "CAST(xp AS INT)";

        $members = Account::orderBy('rank', 'ASC')->orderBy('level', 'DESC')->orderByRaw($castXpAsInt, 'DESC')->get();

        return view('hiscore', compact('members'));
    }

    /**
     * Show the latest member updates.
     *
     * @return
     */
    public function updateLog() {
        $updates = Account::orderBy('updated_at', 'DESC')->whereColumn('updated_at', '>', 'created_at')->get();

        return view('update-log', compact('updates'));
    }
}
