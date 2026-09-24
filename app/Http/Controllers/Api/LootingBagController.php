<?php

namespace App\Http\Controllers\Api;

use App\Events\AccountDataUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateLootingBagRequest;
use App\Models\LootingBag;
use Illuminate\Http\JsonResponse;

class LootingBagController extends Controller
{
    /**
     * Snapshot upsert from the RuneLite plugin. Account resolved by plugin.account middleware.
     */
    public function update(UpdateLootingBagRequest $request): JsonResponse
    {
        $account = $request->attributes->get('account');

        $bag = LootingBag::where('account_id', $account->id)->first()
            ?? (new LootingBag)->forceFill(['account_id' => $account->id]);

        // Store under snake_case to match the model's $fillable and the show()/Resource.
        $bag->looting_bag = $request->validated('looting_bag');
        $bag->save();

        broadcast(new AccountDataUpdated($account, 'looting_bag'));

        return response()->json(['data' => $bag]);
    }
}
