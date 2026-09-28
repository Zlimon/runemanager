<?php

namespace App\Http\Controllers;

use App\Services\WiseOldMan\WomLeaderboard;
use App\Services\WiseOldMan\WomMetrics;
use App\Services\WiseOldMan\WomProgress;
use App\Services\WiseOldMan\WomSnapshotHistory;
use App\Support\AccountNames;
use App\Support\Instance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProgressController extends Controller
{
    /**
     * The instance's Wise Old Man progress: headline tiles, the period's top
     * gainers of one skill, boss or activity, and the top gainer of every
     * skill. Open to guests only when the owner made it public. `period` and
     * `metric` are optional query params that fall back to their default.
     */
    public function index(Request $request, WomProgress $progress, WomLeaderboard $leaderboard): Response|RedirectResponse
    {
        abort_unless(Instance::womEnabled(), 404);

        if (! Instance::womProgressPublic() && $request->user() === null) {
            return redirect()->guest(route('login'));
        }

        $metric = $request->query('metric');
        $filters = [
            'period' => $this->option($request, 'period', array_keys(WomSnapshotHistory::PERIODS), 'week'),
            'metric' => is_string($metric) && WomMetrics::isValid($metric) ? $metric : 'overall',
        ];

        $masked = AccountNames::maskedFor($request->user());

        return Inertia::render('Progress/Index', [
            'filters' => $filters,
            'anonymized' => $masked,
            'metrics' => fn () => $this->metricOptions(),
            'stats' => fn () => $progress->stats(),
            'topGainers' => Inertia::defer(fn () => $this->forViewer(
                $leaderboard->topGainers($filters['period'], $filters['metric']), $masked,
            ), 'gainers'),
            'skillLeaders' => Inertia::defer(fn () => $this->forViewer(
                $leaderboard->skillLeaders($filters['period']), $masked,
            ), 'leaders'),
        ]);
    }

    /**
     * The metric picker's options, grouped as WOM groups them.
     *
     * @return array{skills: list<array{metric: string, label: string, icon: array<string, string>|null}>, bosses: list<array{metric: string, label: string, icon: array<string, string>|null}>, activities: list<array{metric: string, label: string, icon: array<string, string>|null}>}
     */
    private function metricOptions(): array
    {
        $options = fn (array $metrics): array => array_map(fn (string $metric): array => [
            'metric' => $metric,
            'label' => WomMetrics::label($metric),
            'icon' => WomMetrics::icon($metric),
        ], $metrics);

        return [
            'skills' => $options(WomMetrics::SKILLS),
            'bosses' => $options(WomMetrics::BOSSES),
            'activities' => $options(WomMetrics::ACTIVITIES),
        ];
    }

    /**
     * Board rows as this viewer may see them: with "Hide account names" on, a
     * guest gets masked names and no account ids.
     *
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function forViewer(array $rows, bool $masked): array
    {
        if (! $masked) {
            return $rows;
        }

        return array_map(function (array $row): array {
            unset($row['account_id']);
            $row['username'] = AccountNames::mask((string) $row['username']);

            return $row;
        }, $rows);
    }

    /**
     * @param  list<string>  $allowed
     */
    private function option(Request $request, string $key, array $allowed, string $default): string
    {
        $value = $request->query($key);

        return is_string($value) && in_array($value, $allowed, true) ? $value : $default;
    }
}
