<?php

namespace App\Http\Controllers\Api;

use App\Events\AccountDataUpdated;
use App\Http\Controllers\Controller;
use App\Models\LootingBag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LootingBagController extends Controller
{
    /**
     * Snapshot upsert from the RuneLite plugin. Account resolved by plugin.account middleware.
     */
    public function update(Request $request): JsonResponse
    {
        $account = $request->attributes->get('account');

        $request->validate([
            'looting_bag' => ['required', 'array', 'max:28'],
            'looting_bag.*' => ['required', 'array', 'max:2'],
            'looting_bag.*.0' => ['required', 'integer'],
            'looting_bag.*.1' => ['required', 'integer'],
        ]);

        $bag = LootingBag::where('account_id', $account->id)->first()
            ?? (new LootingBag)->forceFill(['account_id' => $account->id]);

        // Store under snake_case to match the model's $fillable and the show()/Resource.
        $bag->looting_bag = $request->input('looting_bag');
        $bag->save();

        broadcast(new AccountDataUpdated($account, 'looting_bag'));

        return response()->json(['data' => $bag]);
    }
}
