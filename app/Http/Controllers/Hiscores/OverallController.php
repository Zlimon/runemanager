<?php

namespace App\Http\Controllers\Hiscores;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterHiscoresRequest;
use App\Services\Hiscores\HiscoreLeaderboard;
use App\Services\Hiscores\LeaderboardPage;
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
    public function index(FilterHiscoresRequest $request, HiscoreLeaderboard $leaderboard, LeaderboardPage $page): Response
    {
        return Inertia::render('Hiscores/Skills/Show', [
            'skillName' => 'Overall',
            'skillSlug' => 'overall',
            'hiscores' => $page->of($leaderboard->skill('overall', 0), $request),
            'filters' => $request->filters(),
        ]);
    }
}
