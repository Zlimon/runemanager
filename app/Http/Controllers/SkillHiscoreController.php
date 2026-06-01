<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountResource;
use App\Models\AccountHiscore;
use App\Models\Skill;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SkillHiscoreController extends Controller
{
    public function index(string $skill): Response
    {
        $skillRecord = Skill::whereSlug($skill)->firstOrFail();

        $hiscores = AccountHiscore::with('account.user')
            ->get()
            ->map(function (AccountHiscore $row) use ($skillRecord) {
                $entry = $row->entries['skills'][$skillRecord->slug] ?? null;

                return [
                    'account' => (new AccountResource($row->account))->resolve(),
                    'rank' => $entry['rank'] ?? 0,
                    'level' => $entry['level'] ?? 1,
                    'xp' => $entry['xp'] ?? 0,
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
                if ($a['level'] !== $b['level']) {
                    return $b['level'] <=> $a['level'];
                }

                return $b['xp'] <=> $a['xp'];
            })
            ->values()
            ->all();

        return Inertia::render('Hiscores/Skills/Show', [
            'recordTypeProp' => 'skill',
            'skillNameProp' => $skillRecord->name,
            'skillSlugProp' => $skillRecord->slug,
            'hiscoresProp' => $hiscores,
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
