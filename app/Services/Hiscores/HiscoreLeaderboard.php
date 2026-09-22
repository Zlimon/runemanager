<?php

namespace App\Services\Hiscores;

use App\Http\Resources\AccountResource;
use App\Models\AccountHiscore;

/**
 * Instance leaderboards built from the synced OSRS hiscore entries. Rows are
 * ordered like the official boards: by OSRS rank, with unranked accounts
 * (rank 0) after every ranked one, then by the board's own tie-breaks.
 */
class HiscoreLeaderboard
{
    /**
     * Leaderboard for one skill (or the "overall" pseudo-skill), tie-broken by
     * level then XP. $unrankedLevel is the level shown for an account with no
     * entry for the skill: 1 for a real skill, 0 for the overall total.
     *
     * @return array<int, array{account: array<string, mixed>, rank: int, level: int, xp: int}>
     */
    public function skill(string $slug, int $unrankedLevel = 1): array
    {
        return AccountHiscore::with('account.user')
            ->get()
            ->map(function (AccountHiscore $row) use ($slug, $unrankedLevel): array {
                $entry = $row->entries['skills'][$slug] ?? null;

                return [
                    'account' => (new AccountResource($row->account))->resolve(),
                    'rank' => $entry['rank'] ?? 0,
                    'level' => $entry['level'] ?? $unrankedLevel,
                    'xp' => $entry['xp'] ?? 0,
                ];
            })
            ->sort(fn (array $a, array $b): int => $this->compareByRank($a, $b)
                ?: [$b['level'], $b['xp']] <=> [$a['level'], $a['xp']])
            ->values()
            ->all();
    }

    /**
     * Leaderboard for one boss, clue tier or other activity, tie-broken by
     * kill count (the activity's hiscore score).
     *
     * @return array<int, array{account: array<string, mixed>, rank: int, kill_count: int}>
     */
    public function activity(string $key): array
    {
        return AccountHiscore::with('account.user')
            ->get()
            ->map(function (AccountHiscore $row) use ($key): array {
                $entry = $row->entries['activities'][$key] ?? null;

                return [
                    'account' => (new AccountResource($row->account))->resolve(),
                    'rank' => $entry['rank'] ?? 0,
                    'kill_count' => $entry['score'] ?? 0,
                ];
            })
            ->sort(fn (array $a, array $b): int => $this->compareByRank($a, $b)
                ?: $b['kill_count'] <=> $a['kill_count'])
            ->values()
            ->all();
    }

    /**
     * Ascending rank with unranked (rank 0) rows last; 0 when the ranks tie.
     *
     * @param  array{rank: int}  $a
     * @param  array{rank: int}  $b
     */
    private function compareByRank(array $a, array $b): int
    {
        if (($a['rank'] === 0) !== ($b['rank'] === 0)) {
            return $a['rank'] === 0 ? 1 : -1;
        }

        return $a['rank'] <=> $b['rank'];
    }
}
