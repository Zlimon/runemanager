<?php

namespace App\Http\Controllers;

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
}
