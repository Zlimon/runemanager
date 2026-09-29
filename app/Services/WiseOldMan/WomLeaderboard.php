<?php

namespace App\Services\WiseOldMan;

use App\Models\Account;
use Illuminate\Support\Facades\Cache;

/**
 * Wise Old Man boards over the /progress population (active, linked and not
 * hidden by the owner): who gained the most of one skill, boss or activity
 * over a period, and who gained the most of each skill.
 */
class WomLeaderboard
{
    /** Top gainers back the dashboard, every member's landing page. */
    public const GAINERS_CACHE_SECONDS = 600;

    public function __construct(private WomMetricGains $gains) {}

    /**
     * The biggest gainers of `$metric` (any {@see WomMetrics::isValid()}
     * metric, else `overall`) over `$period` (`day`, `week`, `month`,
     * `year`); accounts that gained nothing are left out. Cached for
     * {@see self::GAINERS_CACHE_SECONDS} seconds.
     *
     * @return list<array{account_id: int, username: string, account_type: ?string, gained: int}>
     */
    public function topGainers(string $period, string $metric, int $limit = 10): array
    {
        $metric = WomMetrics::isValid($metric) ? $metric : 'overall';
        $period = WomMetricGains::period($period);

        return Cache::remember(
            WomCache::key("gainers:{$period}:{$metric}:{$limit}"),
            self::GAINERS_CACHE_SECONDS,
            function () use ($period, $metric, $limit): array {
                $gains = array_slice($this->gains->forPopulation(WomProgress::population(), $period, $metric), 0, $limit, true);
                $accounts = $this->accounts(array_keys($gains));

                $rows = [];
                foreach ($gains as $accountId => $gained) {
                    if (isset($accounts[$accountId])) {
                        $rows[] = [...$accounts[$accountId], 'gained' => $gained];
                    }
                }

                return $rows;
            },
        );
    }

    /**
     * The top gainer of every skill over `$period`, in skill order; skills
     * nobody trained are left out. Cached like the top gainers.
     *
     * @return list<array{metric: string, label: string, icon: array<string, string>|null, account_id: int, username: string, account_type: ?string, gained: int}>
     */
    public function skillLeaders(string $period): array
    {
        $period = WomMetricGains::period($period);

        return Cache::remember(
            WomCache::key("skill-leaders:{$period}"),
            self::GAINERS_CACHE_SECONDS,
            function () use ($period): array {
                $best = [];
                foreach ($this->gains->forPopulationGroup(WomProgress::population(), $period, 'skill') as $accountId => $skills) {
                    foreach ($skills as $metric => $gained) {
                        if ($gained > ($best[$metric]['gained'] ?? 0)) {
                            $best[$metric] = ['account_id' => $accountId, 'gained' => $gained];
                        }
                    }
                }

                $accounts = $this->accounts(array_column($best, 'account_id'));

                $rows = [];
                foreach (WomMetrics::SKILLS as $metric) {
                    $leader = $best[$metric] ?? null;
                    if ($leader !== null && isset($accounts[$leader['account_id']])) {
                        $rows[] = [
                            'metric' => $metric,
                            'label' => WomMetrics::label($metric),
                            'icon' => WomMetrics::icon($metric),
                            ...$accounts[$leader['account_id']],
                            'gained' => $leader['gained'],
                        ];
                    }
                }

                return $rows;
            },
        );
    }

    /**
     * @param  list<int>  $accountIds
     * @return array<int, array{account_id: int, username: string, account_type: ?string}>
     */
    private function accounts(array $accountIds): array
    {
        return Account::query()
            ->whereIn('id', $accountIds)
            ->get(['id', 'username', 'account_type'])
            ->mapWithKeys(fn (Account $account): array => [$account->id => [
                'account_id' => $account->id,
                'username' => $account->username,
                'account_type' => $account->account_type,
            ]])
            ->all();
    }
}
