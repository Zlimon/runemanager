<?php

namespace App\Http\Controllers\Hiscores;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterHiscoresRequest;
use App\Models\Skill;
use App\Services\Hiscores\HiscoreLeaderboard;
use App\Services\Hiscores\LeaderboardPage;
use Inertia\Inertia;
use Inertia\Response;

class SkillController extends Controller
{
    public function index(FilterHiscoresRequest $request, string $skill, HiscoreLeaderboard $leaderboard, LeaderboardPage $page): Response
    {
        $skillRecord = Skill::whereSlug($skill)->firstOrFail();

        return Inertia::render('Hiscores/Skills/Show', [
            'skillName' => $skillRecord->name,
            'skillSlug' => $skillRecord->slug,
            'hiscores' => $page->of($leaderboard->skill($skillRecord->slug), $request),
            'filters' => $request->filters(),
        ]);
    }
}
