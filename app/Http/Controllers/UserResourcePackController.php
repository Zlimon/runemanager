<?php

namespace App\Http\Controllers;

use App\Models\ResourcePack;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Manage the authenticated user's per-user resource pack override.
 *
 * This is the user's own preference only — never touches the instance-global
 * pack (settings.resource_pack_id, managed by the resourcepack:switch artisan).
 * The resolution hierarchy lives in {@see User::effectiveResourcePackId()}.
 */
class UserResourcePackController extends Controller
{
    /**
     * Website / API: set the override by id.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'resource_pack_id' => ['nullable', 'integer', 'exists:resource_packs,id'],
        ]);

        $user = $request->user();
        $user->resource_pack_id = $validated['resource_pack_id'] ?? null;
        $user->save();

        return response()->json([
            'resource_pack_id' => $user->resource_pack_id,
            'effective_resource_pack_id' => $user->effectiveResourcePackId(),
        ]);
    }

    /**
     * Plugin push: set the override by pack name. The plugin reads the active
     * pack name from the RuneLite community Resource Packs plugin and pushes it
     * here so the website mirrors what's active in-game.
     */
    public function updateFromPlugin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
        ]);

        $pack = ResourcePack::where('name', $validated['name'])->first();

        if (! $pack) {
            return response()->json([
                'message' => sprintf('No resource pack named "%s" is installed on this instance.', $validated['name']),
            ], 404);
        }

        $user = $request->user();
        $user->resource_pack_id = $pack->id;
        $user->save();

        return response()->json([
            'resource_pack_id' => $user->resource_pack_id,
            'effective_resource_pack_id' => $user->effectiveResourcePackId(),
        ]);
    }
}
