<?php

namespace App\Http\Controllers;

use App\Account;
use App\Helpers\Helper;
use App\NewsPost;

class PageController extends Controller
{
    public function index()
    {
        $recentPosts = NewsPost::with('user')->with('category')->with('image')->limit(5)->orderByDesc('created_at')->get();

        return view('index', compact('recentPosts'));
    }

    /**
     * Show the latest account updates.
     *
     * @return
     */
    public function updateLog()
    {
        $updates = Account::orderByDesc('updated_at')->whereColumn('updated_at', '>', 'created_at')->get();

        return view('update-log', compact('updates'));
    }

    /**
     * Show the skill hiscores.
     *
     * @return
     */
    public function hiscore($hiscoreType, $hiscoreName)
    {
        $hiscoreList = Helper::listSkills();

        array_push($hiscoreList, "overall");

        if ($hiscoreType == "boss") {
            $hiscoreList = Helper::listBosses();
            $hiscoreList = array_values($hiscoreList);
        }

        list($hiscoreListTop, $hiscoreListBottom) = array_chunk($hiscoreList,
            ceil(count($hiscoreList) / 2)); // Split skills array into two arrays for a top and bottom skill bar

        $accountCount = Account::count();

        return view('hiscore',
            compact('hiscoreType', 'hiscoreName', 'hiscoreList', 'hiscoreListTop', 'hiscoreListBottom', 'accountCount'));
    }
}
