<?php

namespace App\Http\Controllers\Api;

use App\Events\AccountStatusUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\Account;
use Illuminate\Http\JsonResponse;

class StatusController extends Controller
{
    /**
     * Current in-game activity push from the plugin (Discord-plugin style:
     * "Fishing", "Fighting Vorkath", "Idle"). Stored on the account and broadcast
     * so the cards and profile header update live. Only sent when it changes.
     *
     * The Account is resolved by the plugin.account middleware.
     */
    public function update(UpdateStatusRequest $request): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $validated = $request->validated();

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
