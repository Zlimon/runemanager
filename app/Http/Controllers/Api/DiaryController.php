<?php

namespace App\Http\Controllers\Api;

use App\Events\AccountDataUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateDiariesRequest;
use App\Models\Account;
use App\Models\AccountDiary;
use App\Support\Diaries;
use Illuminate\Http\JsonResponse;

/**
 * Achievement Diary completion push from the plugin. Snapshot upsert
 * of the {area: {tier: bool}} map, normalised against the canonical diary set.
 *
 * The Account is resolved by the plugin.account middleware.
 */
class DiaryController extends Controller
{
    public function update(UpdateDiariesRequest $request): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $diaries = Diaries::normalise((array) $request->validated('diaries'));

        AccountDiary::updateOrCreate(
            ['account_id' => $account->id],
            ['diaries' => $diaries],
        );

        broadcast(new AccountDataUpdated($account, 'diaries'));

        return response()->json(['data' => ['completed' => Diaries::countCompleted($diaries)]]);
    }
}
