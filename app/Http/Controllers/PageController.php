<?php

namespace App\Http\Controllers;

use App\Account;
use App\Collection;
use App\Helpers\Helper;
use App\NewsPost;
use App\Skill;

class PageController extends Controller
{
    /**
     * Show the main page
     *
     * @return
     */
    public function index()
    {
        $recentPosts = NewsPost::with('user')->with('newsCategory')->with('image')->limit(5)->orderByDesc('created_at')->get();

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
     * Show the hiscores
     *
     * @return
     */
    public function hiscore($hiscoreCategory, $hiscoreName)
    {
        switch ($hiscoreCategory) {
            case 'skill':
                $hiscoreList = Helper::listSkills();
                array_push($hiscoreList, 'total');

                if ($hiscoreName != 'total') {
                    $hiscore = Skill::firstWhere('slug', $hiscoreName);
                } else {
                    $hiscore = collect(new Skill);

                    $hiscore->name = 'Total';
                    $hiscore->slug = 'total';
                }
                break;
            case 'boss':
                $hiscoreList = Helper::listBosses();
                $hiscore = Collection::firstWhere('slug', $hiscoreName);
                break;
            case 'npc':
                $hiscoreList = Helper::listNpcs();
                $hiscore = Collection::firstWhere('slug', $hiscoreName);
                break;
            case 'clue':
                $hiscoreList = Helper::listClues();
                $hiscore = Collection::firstWhere('slug', $hiscoreName);
                break;
            default:
                return abort(404);
        }

        if (!$hiscore && $hiscoreName != 'total') {
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
            compact('hiscoreCategory', 'hiscore', 'hiscoreList', 'hiscoreListTop', 'hiscoreListBottom',
                'accountCount'));
    }

    /**
     * Show the calendar
     *
     * @return
     */
    public function calendar()
    {
        return view('calendar');
    }
}
