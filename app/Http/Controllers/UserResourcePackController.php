<?php

namespace App\Http\Controllers;

use App\Jobs\FetchResourcePackJob;
use App\Models\ResourcePack;
use App\Models\User;
use App\Services\ResourcePacks\InstallResourcePack;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
     *
     * Behaviour:
     * - If the pack is already installed (DB row + assets on disk) → 200.
     * - If the pack row doesn't exist or assets are missing → stub-create the
     *   row if needed, queue a {@see FetchResourcePackJob}, record the user's
     *   preference, return 202. Once the job lands, the user's next page load
     *   (or focus-refresh) picks up the assets.
     */
    public function updateFromPlugin(Request $request, InstallResourcePack $installer): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9_-]+$/'],
        ]);

        $name = $validated['name'];

        // Find or stub-create a ResourcePack row so we have an id to point at.
        $pack = ResourcePack::where('name', $name)->first();
        $isStub = false;

        if (! $pack) {
            $pack = new ResourcePack;
            $pack->name = $name;
            $pack->alias = Str::title(str_replace(['pack-', '-'], ' ', $name));
            $pack->version = 'pending';
            $pack->author = 'pending';
            $pack->url = sprintf('https://github.com/melkypie/resource-packs/archive/%s.zip', $name);
            $pack->tags = '';
            $pack->dark_mode = false;
            $pack->save();
            $isStub = true;
        }

        // Record the user's preference even if the pack isn't yet installed —
        // the Blade root will fall through to the default until assets arrive.
        $user = $request->user();
        $user->resource_pack_id = $pack->id;
        $user->save();

        $installed = $installer->isInstalled($name);

        if (! $installed) {
            FetchResourcePackJob::dispatch($name);
        }

        return response()->json([
            'resource_pack_id' => $user->resource_pack_id,
            'effective_resource_pack_id' => $user->effectiveResourcePackId(),
            'installed' => $installed,
            'queued' => ! $installed,
        ], $installed ? 200 : 202);
    }
}
