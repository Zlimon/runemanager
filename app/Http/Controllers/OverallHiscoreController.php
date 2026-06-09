<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountResource;
use App\Models\AccountHiscore;
use Inertia\Inertia;
use Inertia\Response;

class OverallHiscoreController extends Controller
{
    /**
     * SPEC §7.1 — Overall leaderboard (total level + total XP). Reads the
     * "overall" pseudo-skill the hiscores sync stores, ranked like the OSRS
     * overall board (by rank, then total level, then total XP). Reuses the
     * skill leaderboard page — its Level/XP columns become total level / total XP.
     */
    public function index(): Response
    {
        $hiscores = AccountHiscore::with('account.user')
            ->get()
            ->map(function (AccountHiscore $row): array {
                $entry = $row->entries['skills']['overall'] ?? null;

                return [
                    'account' => (new AccountResource($row->account))->resolve(),
                    'rank' => $entry['rank'] ?? 0,
                    'level' => $entry['level'] ?? 0,
                    'xp' => $entry['xp'] ?? 0,
                ];
            })
            ->sort(function (array $a, array $b): int {
                if ($a['rank'] === 0 && $b['rank'] !== 0) {
                    return 1;
                }
                if ($a['rank'] !== 0 && $b['rank'] === 0) {
                    return -1;
                }
                if ($a['rank'] !== $b['rank']) {
                    return $a['rank'] <=> $b['rank'];
                }
                if ($a['level'] !== $b['level']) {
                    return $b['level'] <=> $a['level'];
                }

                return $b['xp'] <=> $a['xp'];
            })
            ->values()
            ->all();

        return Inertia::render('Hiscores/Skills/Show', [
            'recordType' => 'overall',
            'skillName' => 'Overall',
            'skillSlug' => 'overall',
            'hiscores' => $hiscores,
        ]);
    }
}
