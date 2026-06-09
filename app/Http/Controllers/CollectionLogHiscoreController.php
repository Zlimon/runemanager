<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Models\CollectionLog;
use Inertia\Inertia;
use Inertia\Response;

/**
 * SPEC §7 — Collection Log leaderboard: total unique slots unlocked, sourced
 * from the TempleOSRS-synced CollectionLog documents.
 */
class CollectionLogHiscoreController extends Controller
{
    public function index(): Response
    {
        $logs = CollectionLog::all();

        $accounts = Account::with('user')
            ->whereIn('id', $logs->pluck('account_id')->filter()->all())
            ->get()
            ->keyBy('id');

        $hiscores = $logs
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
            ->sortByDesc('obtained')
            ->values()
            ->all();

        return Inertia::render('Hiscores/CollectionLog/Show', [
            'hiscores' => $hiscores,
        ]);
    }
}
