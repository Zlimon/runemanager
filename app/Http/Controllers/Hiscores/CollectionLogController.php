<?php

namespace App\Http\Controllers\Hiscores;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterHiscoresRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Models\CollectionLog;
use App\Services\Hiscores\LeaderboardPage;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Collection Log leaderboard: total unique slots unlocked, sourced
 * from the TempleOSRS-synced CollectionLog documents.
 */
class CollectionLogController extends Controller
{
    public function index(FilterHiscoresRequest $request, LeaderboardPage $page): Response
    {
        $logs = CollectionLog::all();

        $accounts = Account::with('user')
            ->whereIn('id', $logs->pluck('account_id')->filter()->all())
            ->get()
            ->keyBy('id');

        $hiscores = $page->rankedBy(
            $logs
                ->map(function (CollectionLog $log) use ($accounts): ?array {
                    $account = $accounts->get($log->account_id);
                    if ($account === null) {
                        return null;
                    }

                    return [
                        'account' => (new AccountResource($account))->resolve(),
                        'obtained' => (int) $log->obtained,
                        'total' => (int) $log->total,
                    ];
                })
                ->filter()
                ->all(),
            'obtained',
        );

        return Inertia::render('Hiscores/CollectionLog/Show', [
            'hiscores' => $page->of($hiscores, $request),
            'filters' => $request->filters(),
        ]);
    }
}
