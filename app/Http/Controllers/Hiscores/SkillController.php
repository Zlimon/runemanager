<?php

namespace App\Http\Controllers\Hiscores;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Services\Hiscores\HiscoreLeaderboard;
use Inertia\Inertia;
use Inertia\Response;

class SkillController extends Controller
{
    public function index(string $skill, HiscoreLeaderboard $leaderboard): Response
    {
        $skillRecord = Skill::whereSlug($skill)->firstOrFail();

        return Inertia::render('Hiscores/Skills/Show', [
            'recordType' => 'skill',
            'skillName' => $skillRecord->name,
            'skillSlug' => $skillRecord->slug,
            'hiscores' => $leaderboard->skill($skillRecord->slug),
        ]);
    }
}
