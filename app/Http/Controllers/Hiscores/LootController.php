<?php

namespace App\Http\Controllers\Hiscores;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterHiscoresRequest;
use App\Services\Hiscores\LeaderboardPage;
use App\Services\Loot\LootLeaderboard;
use Inertia\Inertia;
use Inertia\Response;

class LootController extends Controller
{
    /**
     * Loot directory. Lists every unique loot source grouped into category
     * sections by the plugin-recorded type. Drilling in hits {@see show()}.
     */
    public function index(LootLeaderboard $leaderboard): Response
    {
        return Inertia::render('Hiscores/Loot/Index', [
            'groups' => $leaderboard->sources(),
        ]);
    }

    /**
     * Per-source board for one (source, type) pair, ranked by total value.
     * The 404 is about the source, not the filter: a source with loot but no
     * account matching the search still renders, with an empty page.
     */
    public function show(FilterHiscoresRequest $request, string $type, string $source, LootLeaderboard $leaderboard, LeaderboardPage $page): Response
    {
        $recordType = LootLeaderboard::typeForToken($type);
        $hiscores = $leaderboard->forSource($recordType, $source);

        abort_if($hiscores === null, 404);

        return Inertia::render('Hiscores/Loot/Show', [
            'source' => $source,
            'category' => LootLeaderboard::categoryFor($recordType),
            'hiscores' => $page->of($page->rankedBy($hiscores, 'total_value'), $request),
            'filters' => $request->filters(),
        ]);
    }
}
