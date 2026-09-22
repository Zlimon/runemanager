<?php

namespace App\Http\Controllers\Hiscores;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Models\AccountHiscore;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CollectionController extends Controller
{
    public function index(string $category, string $collection): Response
    {
        $activityKey = str_replace('-', '_', $collection);
        $displayName = Str::title(str_replace('-', ' ', $collection));

        $hiscores = AccountHiscore::with('account.user')
            ->get()
            ->map(function (AccountHiscore $row) use ($activityKey) {
                $entry = $row->entries['activities'][$activityKey] ?? null;

                return [
                    'account' => (new AccountResource($row->account))->resolve(),
                    'rank' => $entry['rank'] ?? 0,
                    'kill_count' => $entry['score'] ?? 0,
                ];
            })
            ->sort(function (array $a, array $b) {
                if ($a['rank'] === 0 && $b['rank'] !== 0) {
                    return 1;
                }
                if ($a['rank'] !== 0 && $b['rank'] === 0) {
                    return -1;
                }
                if ($a['rank'] !== $b['rank']) {
                    return $a['rank'] <=> $b['rank'];
                }

                return $b['kill_count'] <=> $a['kill_count'];
            })
            ->values()
            ->all();

        return Inertia::render('Hiscores/Bosses/Show', [
            'recordType' => $category,
            'collectionName' => $displayName,
            'collectionSlug' => $collection,
            'hiscores' => $hiscores,
        ]);
    }
}
