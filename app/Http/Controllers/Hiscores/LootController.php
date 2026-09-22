<?php

namespace App\Http\Controllers\Hiscores;

use App\Http\Controllers\Controller;
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
            'recordType' => 'loot',
            'groups' => $leaderboard->sources(),
        ]);
    }

    /**
     * Per-source board for one (source, type) pair, ranked by total value.
     */
    public function show(string $type, string $source, LootLeaderboard $leaderboard): Response
    {
        $recordType = LootLeaderboard::typeForToken($type);
        $hiscores = $leaderboard->forSource($recordType, $source);

        abort_if($hiscores === null, 404);

        return Inertia::render('Hiscores/Loot/Show', [
            'recordType' => 'loot',
            'source' => $source,
            'category' => LootLeaderboard::categoryFor($recordType),
            'hiscores' => $hiscores,
        ]);
    }
}
