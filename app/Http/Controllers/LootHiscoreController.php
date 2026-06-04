<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Models\Loot;
use Inertia\Inertia;
use Inertia\Response;

class LootHiscoreController extends Controller
{
    /**
     * SPEC §7.1 — Loot leaderboard ranked by total loot value (sum of each
     * drop's plugin-computed value). Aggregated in Mongo, then joined with the
     * Postgres accounts for display.
     */
    public function index(): Response
    {
        $totals = collect(
            (new Loot)->getConnection()
                ->getDatabase()
                ->selectCollection((new Loot)->getTable())
                ->aggregate([
                    ['$group' => [
                        '_id' => '$account_id',
                        'total_value' => ['$sum' => '$total_value'],
                        'drops' => ['$sum' => 1],
                    ]],
                    ['$sort' => ['total_value' => -1]],
                ])
                ->toArray(),
        )->map(fn ($row): array => [
            'account_id' => (int) $row->_id,
            'total_value' => (int) $row->total_value,
            'drops' => (int) $row->drops,
        ]);

        $accounts = Account::with('user')
            ->whereIn('id', $totals->pluck('account_id'))
            ->get()
            ->keyBy('id');

        $hiscores = $totals
            ->filter(fn (array $total): bool => $accounts->has($total['account_id']))
            ->values()
            ->map(fn (array $total, int $index): array => [
                'rank' => $index + 1,
                'account' => (new AccountResource($accounts[$total['account_id']]))->resolve(),
                'total_value' => $total['total_value'],
                'drops' => $total['drops'],
            ])
            ->all();

        return Inertia::render('Hiscores/Loot/Show', [
            'recordType' => 'loot',
            'hiscores' => $hiscores,
        ]);
    }
}
