<?php

namespace App\Http\Controllers\Hiscores;

use App\Http\Controllers\Controller;
use App\Services\Hiscores\HiscoreLeaderboard;
use Inertia\Inertia;
use Inertia\Response;

class OverallController extends Controller
{
    /**
     * Overall leaderboard (total level + total XP). Reads the
     * "overall" pseudo-skill the hiscores sync stores, ranked like the OSRS
     * overall board (by rank, then total level, then total XP). Reuses the
     * skill leaderboard page — its Level/XP columns become total level / total XP.
     */
    public function index(HiscoreLeaderboard $leaderboard): Response
    {
        return Inertia::render('Hiscores/Skills/Show', [
            'recordType' => 'overall',
            'skillName' => 'Overall',
            'skillSlug' => 'overall',
            'hiscores' => $leaderboard->skill('overall', 0),
        ]);
    }
}
