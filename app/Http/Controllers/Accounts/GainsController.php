<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\Accounts\AccountProfile;
use App\Services\WiseOldMan\WomAccountView;
use App\Services\WiseOldMan\WomMetricGains;
use App\Services\WiseOldMan\WomMetrics;
use App\Services\WiseOldMan\WomSnapshotHistory;
use App\Support\Instance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Skill, boss and activity gains from Wise Old Man, with the account's
 * personal records and achievements. Only exists while the integration is on.
 */
class GainsController extends Controller
{
    public function show(Request $request, Account $account, AccountProfile $profile, WomAccountView $womView, WomMetricGains $gains): Response
    {
        abort_unless(Instance::womEnabled(), 404);

        $account->loadMissing('womPlayer');
        $filters = $this->filters($request);

        return Inertia::render('Accounts/Gains/Show', [
            ...$profile->header($account, $request->user()),

            'wom' => fn () => $womView->summary($account, $request->user()),

            'filters' => $filters,

            'gains' => Inertia::defer(fn () => $gains->forAccount($account, $filters['period']), 'gains'),

            'metricSeries' => Inertia::defer(fn () => [
                'metric' => $filters['metric'],
                'label' => WomMetrics::label($filters['metric']),
                'points' => $gains->series($account, $filters['metric'], $filters['range']),
            ], 'series'),

            'records' => fn () => $womView->records($account),

            'achievements' => fn () => $womView->achievements($account),
        ]);
    }

    /**
     * Optional query params; anything unknown falls back to its default.
     *
     * @return array{period: string, metric: string, range: string}
     */
    private function filters(Request $request): array
    {
        $period = $request->query('period');
        $metric = $request->query('metric');
        $range = $request->query('range');

        return [
            'period' => is_string($period) && array_key_exists($period, WomSnapshotHistory::PERIODS) ? $period : 'week',
            'metric' => is_string($metric) && WomMetrics::isValid($metric) ? $metric : 'overall',
            'range' => is_string($range) && array_key_exists($range, WomSnapshotHistory::RANGES) ? $range : 'year',
        ];
    }
}
