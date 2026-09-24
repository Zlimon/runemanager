<?php

namespace App\Services\Hiscores;

use App\Http\Resources\AccountResource;
use App\Models\AccountHiscore;
use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;

/**
 * Instance leaderboards built from the synced OSRS hiscore entries. Rows are
 * ordered like the official boards: by OSRS rank, with unranked accounts
 * after every ranked one, then by the board's own tie-breaks.
 *
 * The OSRS hiscores report an unranked skill or activity as rank -1 (and XP
 * or score -1), which is stored as-is; every row shows it as 0.
 *
 * A board is always returned whole and in rank order, down to a final
 * username tie-break ({@see LeaderboardPage::compareByUsername()}) that gives
 * it a total order — without one, rows tying on every metric would be ordered
 * by the database's whim, differently on each request. Narrowing the board to
 * a search term and slicing it into pages is {@see LeaderboardPage}'s job, and
 * happens after this, so a row's rank never depends on the page or the filter.
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
        $rows = AccountHiscore::with('account.user')->get();
        $icons = $this->iconsFor($rows);

        return $rows
            ->map(function (AccountHiscore $row) use ($slug, $unrankedLevel, $icons): array {
                $entry = $row->entries['skills'][$slug] ?? null;

                return [
                    'account' => (new AccountResource($row->account))->withIcons($icons)->resolve(),
                    'rank' => max(0, $entry['rank'] ?? 0),
                    'level' => $entry['level'] ?? $unrankedLevel,
                    'xp' => max(0, $entry['xp'] ?? 0),
                ];
            })
            ->sort(fn (array $a, array $b): int => $this->compareByRank($a, $b)
                ?: [$b['level'], $b['xp']] <=> [$a['level'], $a['xp']]
                ?: LeaderboardPage::compareByUsername($a, $b))
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
        $rows = AccountHiscore::with('account.user')->get();
        $icons = $this->iconsFor($rows);

        return $rows
            ->map(function (AccountHiscore $row) use ($key, $icons): array {
                $entry = $row->entries['activities'][$key] ?? null;

                return [
                    'account' => (new AccountResource($row->account))->withIcons($icons)->resolve(),
                    'rank' => max(0, $entry['rank'] ?? 0),
                    'kill_count' => max(0, $entry['score'] ?? 0),
                ];
            })
            ->sort(fn (array $a, array $b): int => $this->compareByRank($a, $b)
                ?: $b['kill_count'] <=> $a['kill_count']
                ?: LeaderboardPage::compareByUsername($a, $b))
            ->values()
            ->all();
    }

    /**
     * Avatar icons for a whole board in one Mongo query. Every row is wrapped
     * on its own here, so without this each one would resolve its owner's icon
     * with an aggregation of its own.
     *
     * @param  Collection<int, AccountHiscore>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function iconsFor(Collection $rows): array
    {
        return Item::lookupByOsrsIds(
            $rows->map(fn (AccountHiscore $row): ?int => $row->account?->user?->icon_id)
                ->filter()
                ->unique()
                ->values()
                ->all(),
        );
    }

    /**
     * Ascending rank with unranked (rank 0 or below) rows last; 0 when the ranks tie.
     *
     * @param  array{rank: int}  $a
     * @param  array{rank: int}  $b
     */
    private function compareByRank(array $a, array $b): int
    {
        if (($a['rank'] <= 0) !== ($b['rank'] <= 0)) {
            return $a['rank'] <= 0 ? 1 : -1;
        }

        return $a['rank'] <=> $b['rank'];
    }
}
