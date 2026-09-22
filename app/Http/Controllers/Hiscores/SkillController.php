<?php

namespace App\Http\Controllers\Hiscores;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Models\AccountHiscore;
use App\Models\Skill;
use Inertia\Inertia;
use Inertia\Response;

class SkillController extends Controller
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
            'recordType' => 'skill',
            'skillName' => $skillRecord->name,
            'skillSlug' => $skillRecord->slug,
            'hiscores' => $hiscores,
        ]);
    }
}
