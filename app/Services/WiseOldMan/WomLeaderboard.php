<?php

namespace App\Services\WiseOldMan;

use App\Models\Account;
use App\Models\WomPlayer;
use Illuminate\Support\Facades\Cache;

/**
 * Wise Old Man boards over the /progress population (active, linked and not
 * hidden by the owner): who gained the most over a period, and who has the
 * most efficient hours.
 */
class WomLeaderboard
{
    /** Metrics a board can rank on. */
    public const GAIN_METRICS = ['exp', 'ehp', 'ehb'];

    /** Top gainers back the dashboard, every member's landing page. */
    public const GAINERS_CACHE_SECONDS = 600;

    public function __construct(private WomSnapshotHistory $history) {}

    /**
     * The biggest gainers of `$metric` (`exp`, `ehp`, `ehb`) over `$period`
     * (`day`, `week`, `month`, `year`); accounts that gained nothing are left
     * out. Cached for {@see self::GAINERS_CACHE_SECONDS} seconds.
     *
     * @return list<array{account_id: int, username: string, account_type: ?string, gained: int|float}>
     */
    public function topGainers(string $period, string $metric, int $limit = 10): array
    {
        $metric = in_array($metric, self::GAIN_METRICS, true) ? $metric : 'exp';
        $period = array_key_exists($period, WomSnapshotHistory::PERIODS) ? $period : 'week';

        return Cache::remember(
            WomCache::key("gainers:{$period}:{$metric}:{$limit}"),
            self::GAINERS_CACHE_SECONDS,
            fn (): array => $this->rankGainers($period, $metric, $limit),
        );
    }

    /**
     * @return list<array{account_id: int, username: string, account_type: ?string, gained: int|float}>
     */
    private function rankGainers(string $period, string $metric, int $limit): array
    {
        $gains = collect($this->history->gained(WomProgress::population(), $period))
            ->map(fn (array $gained): int|float => $gained[$metric])
            ->filter(fn (int|float $gained): bool => $gained > 0)
            ->sortDesc()
            ->take($limit);

        $accounts = Account::query()
            ->whereIn('id', $gains->keys())
            ->get(['id', 'username', 'account_type'])
            ->keyBy('id');

        return $gains
            ->filter(fn (int|float $gained, int $accountId): bool => $accounts->has($accountId))
            ->map(fn (int|float $gained, int $accountId): array => [
                'account_id' => $accountId,
                'username' => $accounts[$accountId]->username,
                'account_type' => $accounts[$accountId]->account_type,
                'gained' => $gained,
            ])
            ->values()
            ->all();
    }

    /**
     * Members ranked by their current EHP or EHB.
     *
     * @return list<array{account_id: int, username: string, account_type: ?string, value: float}>
     */
    public function efficiency(string $metric, int $limit = 25): array
    {
        $metric = $metric === 'ehb' ? 'ehb' : 'ehp';

        return WomPlayer::active()->visibleOnProgress()
            ->with('account:id,username,account_type')
            ->where($metric, '>', 0)
            ->orderByDesc($metric)
            ->limit($limit)
            ->get()
            ->filter(fn (WomPlayer $player): bool => $player->account !== null)
            ->map(fn (WomPlayer $player): array => [
                'account_id' => $player->account_id,
                'username' => $player->account->username,
                'account_type' => $player->account->account_type,
                'value' => round((float) $player->{$metric}, 2),
            ])
            ->values()
            ->all();
    }
}
