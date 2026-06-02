<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountResource;
use App\Models\AccountHiscore;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CollectionHiscoreController extends Controller
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
