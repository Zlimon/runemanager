<?php

namespace App\Http\Controllers\Api;

use App\Events\AccountStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    /**
     * Current in-game activity push from the plugin (Discord-plugin style:
     * "Fishing", "Fighting Vorkath", "Idle"). Stored on the account and broadcast
     * so the cards and profile header update live. Only sent when it changes.
     *
     * The Account is resolved by the plugin.account middleware.
     */
    public function update(Request $request): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $validated = $request->validate([
            'activity' => ['nullable', 'string', 'max:60'],
            'activity_icon' => ['nullable', 'string', 'max:40'],
            'location' => ['nullable', 'string', 'max:60'],
            'world' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $account->forceFill([
            'activity' => $validated['activity'] ?? null,
            'activity_icon' => $validated['activity_icon'] ?? null,
            'location' => $validated['location'] ?? null,
            'world_number' => $validated['world'] ?? null,
        ])->save();

        broadcast(new AccountStatusUpdated($account));

        return response()->json(['data' => [
            'activity' => $account->activity,
            'location' => $account->location,
            'world' => $account->world_number,
        ]]);
    }
}
