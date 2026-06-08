<?php

namespace App\Http\Controllers\Api;

use App\Events\AccountMoved;
use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    /**
     * Live Map position push from the RuneLite plugin (sent while the player has
     * location sharing enabled). Stores the latest WorldPoint; "on the map" is
     * derived from position_updated_at so it expires when sharing stops.
     *
     * The Account is resolved by the plugin.account middleware.
     *
     * Payload: { "x": 3221, "y": 3219, "plane": 0 }
     */
    public function update(Request $request): JsonResponse
    {
        /** @var Account $account */
        $account = $request->attributes->get('account');

        $validated = $request->validate([
            'x' => ['required', 'integer'],
            'y' => ['required', 'integer'],
            'plane' => ['required', 'integer', 'min:0', 'max:3'],
        ]);

        $account->forceFill([
            'world_x' => $validated['x'],
            'world_y' => $validated['y'],
            'world_plane' => $validated['plane'],
            'position_updated_at' => now(),
        ])->save();

        broadcast(new AccountMoved($account));

        return response()->json(['data' => ['position_updated_at' => $account->position_updated_at->toIso8601String()]]);
    }
}
