<?php

namespace App\Http\Controllers\Api;

use App\Events\AccountDataUpdated;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountDiary;
use App\Support\Diaries;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SPEC §5.2 — Achievement Diary completion push from the plugin. Snapshot upsert
 * of the {area: {tier: bool}} map, normalised against the canonical diary set.
 *
 * The Account is resolved by the plugin.account middleware.
 */
class DiaryController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $request->validate([
            'diaries' => ['present', 'array'],
        ]);

        $diaries = Diaries::normalise((array) $request->input('diaries'));

        AccountDiary::updateOrCreate(
            ['account_id' => $account->id],
            ['diaries' => $diaries],
        );

        broadcast(new AccountDataUpdated($account, 'diaries'));

        return response()->json(['data' => ['completed' => Diaries::countCompleted($diaries)]]);
    }
}
