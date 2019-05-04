<?php

namespace RuneManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SkillsController extends Controller
{
    /**
     * Show skill hiscore selector.
     *
     * @return
     */
    public function index() {
        $skills = ["attack","defence","strength","hitpoints","ranged","prayer","magic","cooking","woodcutting","fletching","fishing","firemaking","crafting","smithing","mining","herblore","agility","thieving","slayer","farming","runecrafting","hunter","construction"];

        return view('skill.index', compact('skills'));
    }

    /**
     * Show a specific skill from a URL request.
     *
     * @param  string  $skillname
     * @return
     */
    public function show($skillname) {
        $castXpAsInt = 'CAST('.$skillname.'.xp AS INT)';

        $hiscores = DB::table($skillname)
            ->select($skillname.'.account_id', $skillname.'.level', $skillname.'.xp', $skillname.'.rank', 'username')
            ->join('accounts', $skillname.'.account_id', '=', 'accounts.id')
            ->orderBy('rank', 'ASC')
            ->orderBy('level', 'DESC')
            ->orderByRaw($castXpAsInt, 'DESC')
            ->get();

        return view('skill.show', compact('hiscores', 'skillname'));
    }
}
