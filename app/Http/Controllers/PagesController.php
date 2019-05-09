<?php

namespace RuneManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuneManager\NewsPost;
use RuneManager\Account;

class PagesController extends Controller
{
    public function index() {
        //$recentMembers = Account::orderBy('created_at', 'DESC')->limit(5)->get();
        $recentPosts = NewsPost::with('user')->with('category')->limit(5)->orderBy('created_at', 'DESC')->get();

        return view('index', compact('recentPosts'));
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

    /**
     * Show the application hiscores.
     *
     * @return
     */
    public function hiscore($skillname) {
        $skillsTop = ["overall", "attack","defence","strength","hitpoints","ranged","prayer","magic","cooking","woodcutting","fletching","fishing"];

        $skillsBottom = ["firemaking","crafting","smithing","mining","herblore","agility","thieving","slayer","farming","runecrafting","hunter","construction"];

        $skills = ["attack","defence","strength","hitpoints","ranged","prayer","magic","cooking","woodcutting","fletching","fishing","firemaking","crafting","smithing","mining","herblore","agility","thieving","slayer","farming","runecrafting","hunter","construction"];

        if ($skillname == "overall") {
            $hiscores = Account::orderBy('rank', 'ASC')->orderBy('level', 'DESC')->orderByRaw('CAST(xp AS INT)', 'DESC')->get();

            $sumTotalXp = Account::sum('xp');

            $averageTotalLevel = Account::sum('level') / Account::count();

            $totalMaxLevel = Account::where('level', (99 * count($skills)))->count();
        } else {
            $sumTotalXp = DB::table($skillname)
                ->selectRaw('SUM(xp) AS total_xp')
                ->first();

            $sumTotalXp = $sumTotalXp->total_xp;

            $sumTotalLevel = DB::table($skillname)
                ->selectRaw('SUM(level) AS total_level')
                ->selectRaw('COUNT(*) AS total_hiscores')
                ->first();

            $averageTotalLevel = $sumTotalLevel->total_level / $sumTotalLevel->total_hiscores;

            $totalMaxLevel = DB::table($skillname)
                ->selectRaw('COUNT(*) AS amount_99')
                ->where('level', 99)
                ->first();

            $totalMaxLevel = $totalMaxLevel->amount_99;

            $hiscores = DB::table($skillname)
                ->select($skillname.'.account_id', $skillname.'.level', $skillname.'.xp', $skillname.'.rank', 'username')
                ->join('accounts', $skillname.'.account_id', '=', 'accounts.id')
                ->orderBy('rank', 'ASC')
                ->orderBy('level', 'DESC')
                ->orderByRaw('CAST('.$skillname.'.xp AS INT)', 'DESC')
                ->get();
        }

        return view('hiscore', compact('skillsTop', 'skillsBottom', 'skills', 'sumTotalXp', 'averageTotalLevel', 'totalMaxLevel', 'hiscores', 'skillname'));
    }
}
