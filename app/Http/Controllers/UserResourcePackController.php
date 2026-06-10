<?php

namespace App\Http\Controllers;

use App\Helpers\SettingHelper;
use App\Jobs\FetchResourcePackJob;
use App\Models\ResourcePack;
use App\Models\User;
use App\Services\ResourcePacks\InstallResourcePack;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

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
     * SPEC §6.2 — the user's appearance page: browse installed packs (with
     * icon.png thumbnails) and pick a personal override.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Themes/Index', [
            'packs' => ResourcePack::query()->orderBy('alias')->get()
                ->map(fn (ResourcePack $pack): array => $pack->toPickerArray())
                ->all(),
            'selectedId' => $request->user()->resource_pack_id,
            'defaultId' => ((int) SettingHelper::getSetting('resource_pack_id', 0)) ?: null,
        ]);
    }

    /**
     * Website / API: set the override by id. Inertia requests get a redirect
     * back (the picker full-reloads to swap the server-rendered pack CSS); API
     * callers get JSON.
     */
    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'resource_pack_id' => ['nullable', 'integer', 'exists:resource_packs,id'],
        ]);

        $user = $request->user();
        $user->resource_pack_id = $validated['resource_pack_id'] ?? null;
        $user->save();

        if ($request->header('X-Inertia')) {
            return back();
        }

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

        // "Installed" means BOTH assets on disk AND a fully populated DB row.
        // A stub row (just created above, or carried over from a previous
        // migrate:fresh while public/resource-packs/ survived) carries
        // version=pending and null colour columns — the install pipeline needs
        // to run to fetch metadata + extract the palette even if the CSS file
        // happens to already be sitting on disk.
        $installed = $installer->isInstalled($name) && $pack->version !== 'pending';

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
