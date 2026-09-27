<?php

namespace App\Http\Controllers;

use App\Services\WiseOldMan\WomLeaderboard;
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
     * The instance's Wise Old Man progress: headline tiles, the members'
     * summed history, the period's top gainers and the EHP/EHB boards. Open
     * to guests only when the owner made it public. `range`, `period` and
     * `metric` are optional query params that fall back to their default.
     */
    public function index(Request $request, WomProgress $progress, WomLeaderboard $leaderboard): Response|RedirectResponse
    {
        abort_unless(Instance::womEnabled(), 404);

        if (! Instance::womProgressPublic() && $request->user() === null) {
            return redirect()->guest(route('login'));
        }

        $filters = [
            'range' => $this->option($request, 'range', array_keys(WomSnapshotHistory::RANGES), 'year'),
            'period' => $this->option($request, 'period', array_keys(WomSnapshotHistory::PERIODS), 'week'),
            'metric' => $this->option($request, 'metric', WomLeaderboard::GAIN_METRICS, 'exp'),
        ];

        $masked = AccountNames::maskedFor($request->user());

        return Inertia::render('Progress/Index', [
            'filters' => $filters,
            'anonymized' => $masked,
            'stats' => fn () => $progress->stats(),
            'series' => Inertia::defer(fn () => $progress->instanceSeries($filters['range']), 'series'),
            'topGainers' => Inertia::defer(fn () => $this->forViewer(
                $leaderboard->topGainers($filters['period'], $filters['metric']), $masked,
            ), 'gainers'),
            'efficiency' => Inertia::defer(fn () => [
                'ehp' => $this->forViewer($leaderboard->efficiency('ehp'), $masked),
                'ehb' => $this->forViewer($leaderboard->efficiency('ehb'), $masked),
            ], 'efficiency'),
        ]);
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
