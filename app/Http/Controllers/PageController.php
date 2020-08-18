<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Helpers\Helper;
use App\NewsPost;
use App\Account;
use App\Image;

class PageController extends Controller
{
    public function index() {
        //$recentMembers = Account::orderBy('created_at', 'DESC')->limit(5)->get();
        $recentPosts = NewsPost::with('user')->with('category')->with('image')->limit(5)->orderBy('created_at', 'DESC')->get();

        return view('index', compact('recentPosts'));
    }

    /**
     * Show the latest account updates.
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
    public function hiscore($skillName) {
        $skills = Helper::listSkills();

        array_push($skills, "overall");

        list($skillsTop, $skillsBottom) = array_chunk($skills, ceil(count($skills) / 2)); // Split skills array into two arrays for a top and bottom skill bar

        return view('hiscore', compact('skillName', 'skills', 'skillsTop', 'skillsBottom'));
    }
}
