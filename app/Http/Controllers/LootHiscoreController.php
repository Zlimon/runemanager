<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Models\Loot;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LootHiscoreController extends Controller
{
    /**
     * SPEC §7.1 — Loot leaderboard ranked by total loot value (sum of each
     * drop's plugin-computed value). Aggregated in Mongo, then joined with the
     * Postgres accounts for display.
     *
     * An optional `?source=` narrows the board to a single loot source (SPEC
     * §7 loot-by-source); without it the board sums every source. Either way the
     * page also receives the list of sources for its selector.
     */
    public function index(Request $request): Response
    {
        $source = $request->string('source')->trim()->value();
        $source = $source !== '' ? $source : null;

        return Inertia::render('Hiscores/Loot/Show', [
            'recordType' => 'loot',
            'sources' => $this->sources(),
            'selectedSource' => $source,
            'hiscores' => $this->leaderboard($source),
        ]);
    }

    /**
     * Per-account totals (value + drop count), optionally filtered to one source,
     * ranked by value and joined with the Postgres accounts.
     *
     * @return array<int, array<string, mixed>>
     */
    private function leaderboard(?string $source): array
    {
        $pipeline = [];
        if ($source !== null) {
            $pipeline[] = ['$match' => ['source' => $source]];
        }
        $pipeline[] = ['$group' => [
            '_id' => '$account_id',
            'total_value' => ['$sum' => '$total_value'],
            'drops' => ['$sum' => 1],
        ]];
        $pipeline[] = ['$sort' => ['total_value' => -1]];

        $totals = collect($this->aggregate($pipeline))
            ->map(fn ($row): array => [
                'account_id' => (int) $row->_id,
                'total_value' => (int) $row->total_value,
                'drops' => (int) $row->drops,
            ]);

        $accounts = Account::with('user')
            ->whereIn('id', $totals->pluck('account_id'))
            ->get()
            ->keyBy('id');

        return $totals
            ->filter(fn (array $total): bool => $accounts->has($total['account_id']))
            ->values()
            ->map(fn (array $total, int $index): array => [
                'rank' => $index + 1,
                'account' => (new AccountResource($accounts[$total['account_id']]))->resolve(),
                'total_value' => $total['total_value'],
                'drops' => $total['drops'],
            ])
            ->all();
    }

    /**
     * Distinct loot sources ordered by combined value, for the page selector.
     *
     * @return array<int, string>
     */
    private function sources(): array
    {
        return collect($this->aggregate([
            ['$group' => ['_id' => '$source', 'total_value' => ['$sum' => '$total_value']]],
            ['$sort' => ['total_value' => -1]],
        ]))
            ->map(fn ($row): ?string => $row->_id)
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $pipeline
     * @return array<int, object>
     */
    private function aggregate(array $pipeline): array
    {
        return (new Loot)->getConnection()
            ->getDatabase()
            ->selectCollection((new Loot)->getTable())
            ->aggregate($pipeline)
            ->toArray();
    }
}
