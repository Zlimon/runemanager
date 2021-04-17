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
     * Show the latest account updates
     *
     * @return
     */
    public function updateLog()
    {
        $updates = Account::orderByDesc('updated_at')->whereColumn('updated_at', '>', 'created_at')->get();

        return view('update-log', compact('updates'));
    }

    /**
     * Show the skill hiscores
     *
     * @return
     */
    public function hiscore($hiscoreType, $hiscoreName)
    {
        switch ($hiscoreType) {
            case "skill":
                $hiscoreList = Helper::listSkills();
                array_push($hiscoreList, "total");
                break;
            case "boss":
                $hiscoreList = Helper::listBosses();
                break;
            case "npc":
                $hiscoreList = Helper::listNpcs();
                break;
            case "clue":
                $hiscoreList = Helper::listClues();
                break;
            default:
                return abort(404);
        }

        if (!in_array($hiscoreName, $hiscoreList)) {
            return abort(404);
        }

        if (count($hiscoreList) > 1) {
            list($hiscoreListTop, $hiscoreListBottom) = array_chunk($hiscoreList,
                ceil(count($hiscoreList) / 2)); // Split skills array into two arrays for a top and bottom skill bar
        } else {
            $hiscoreListTop = $hiscoreList;
            $hiscoreListBottom = $hiscoreList;
        }

        $accountCount = Account::count();

        return view('hiscore',
            compact('hiscoreType', 'hiscoreName', 'hiscoreList', 'hiscoreListTop', 'hiscoreListBottom',
                'accountCount'));
    }
}
