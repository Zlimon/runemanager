<?php

namespace App\Http\Controllers;

use App\Http\Requests\HubPackNameRequest;
use App\Jobs\FetchResourcePackJob;
use App\Models\ResourcePack;
use App\Services\ResourcePacks\DeleteResourcePack;
use App\Services\ResourcePacks\InstallResourcePack;
use App\Services\ResourcePacks\PackInstallQuota;
use App\Services\ResourcePacks\ResourcePackHub;
use App\Services\ResourcePacks\SelectResourcePack;
use App\Support\Instance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
     * Website / API: set the override by id. Inertia requests get a redirect
     * back (the picker full-reloads to swap the server-rendered pack CSS); API
     * callers get JSON.
     */
    public function update(Request $request, SelectResourcePack $selector): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            return $this->updateForGuest($request);
        }

        if ($request->input('resource_pack_id') === ResourcePack::GUEST_NO_PACK) {
            // Explicit "no resource pack": plain interface, clear any selection.
            $selector->select($user, null, true);
        } else {
            $validated = $request->validate([
                'resource_pack_id' => ['nullable', 'integer', 'exists:resource_packs,id'],
            ]);
            $selector->select(
                $user,
                isset($validated['resource_pack_id']) ? (int) $validated['resource_pack_id'] : null,
                false,
            );
        }

        if ($request->header('X-Inertia')) {
            return back();
        }

        return response()->json([
            'resource_pack_id' => $user->resource_pack_id,
            'disable_resource_pack' => $user->disable_resource_pack,
            'effective_resource_pack_id' => $user->effectiveResourcePackId(),
        ]);
    }

    /**
     * The same preference for a logged-out visitor, stored in a long-lived
     * cookie instead of on an account. Guests need this to reach the untextured
     * interface, which is the only one offering a light/dark toggle.
     */
    private function updateForGuest(Request $request): JsonResponse|RedirectResponse
    {
        $choice = $request->input('resource_pack_id');

        if ($choice === ResourcePack::GUEST_NO_PACK) {
            $value = ResourcePack::GUEST_NO_PACK;
        } else {
            $validated = $request->validate([
                'resource_pack_id' => ['nullable', 'integer', 'exists:resource_packs,id'],
            ]);
            // Null clears the cookie back to "follow the instance default".
            $value = isset($validated['resource_pack_id']) ? (string) $validated['resource_pack_id'] : null;
        }

        $cookie = $value === null
            ? cookie()->forget(Instance::RESOURCE_PACK_COOKIE)
            : cookie(Instance::RESOURCE_PACK_COOKIE, $value, 60 * 24 * 365);

        $response = $request->header('X-Inertia')
            ? back()
            : response()->json(['resource_pack_id' => $value]);

        $response = $response->withCookie($cookie);

        // A guest's light/dark cookie is the same stale explicit choice a member
        // carries on their row; drop it on a real pack change for the same reason
        // (see SelectResourcePack::select()).
        if ((string) $value !== (string) $request->cookie(Instance::RESOURCE_PACK_COOKIE)) {
            $response = $response->withCookie(cookie()->forget(Instance::DARK_MODE_COOKIE));
        }

        return $response;
    }

    /**
     * Website: install a pack from the community hub and apply it as the user's
     * personal theme. Same pipeline as the plugin push, keyed on a hub branch
     * name (`pack-*`). Answers with JSON so the Appearance page can show a
     * spinner and poll {@see status()} until the download lands.
     */
    public function install(HubPackNameRequest $request, SelectResourcePack $selector, PackInstallQuota $quota): JsonResponse
    {
        $validated = $request->validated();

        $user = $request->user();

        // Only a brand-new pack counts against the member's quota — re-adding one
        // already in the shared pool just points them at it.
        if (! ResourcePack::where('name', $validated['name'])->exists() && ! $quota->allows($user)) {
            throw ValidationException::withMessages([
                'name' => "You've reached your limit of {$quota->limit()} installed packs. Delete one to add another.",
            ]);
        }

        $installed = $selector->installAndSelect($user, $validated['name']);

        return response()->json([
            'name' => $validated['name'],
            'installed' => $installed,
            'queued' => ! $installed,
        ]);
    }

    /**
     * Delete a pack the member installed. They can only remove their own installs,
     * never the bundled default or the current instance-wide default (an admin
     * must change that first). Anyone using it falls back to the default.
     */
    public function destroy(Request $request, ResourcePack $pack, DeleteResourcePack $deleter): RedirectResponse
    {
        abort_unless(
            $pack->installed_by_user_id === $request->user()->id,
            403,
            'You can only delete packs you installed.',
        );
        abort_if($pack->isVanilla(), 403, 'The default pack cannot be deleted.');
        abort_if($deleter->isInstanceDefault($pack), 422, 'This pack is the instance default — an admin must change it first.');

        $deleter->run($pack);

        return back();
    }

    /**
     * Website: report whether a pack has finished installing (assets on disk +
     * a fully-populated row). Polled by the Appearance page after an install so
     * it can reload — and apply the new theme — the moment the job lands.
     */
    public function status(HubPackNameRequest $request, InstallResourcePack $installer): JsonResponse
    {
        return response()->json([
            'installed' => $installer->isReady($request->validated('name')),
        ]);
    }

    /**
     * Website: describe a hub pack before it is installed — its author, version
     * and tags from pack.properties. Fetched when the install dialog opens rather
     * than for the whole browse grid, which would mean a request per hub pack on
     * every page view. {@see ResourcePackHub::properties()} caches it and refuses
     * any name the hub does not list.
     */
    public function hub(HubPackNameRequest $request, ResourcePackHub $hub): JsonResponse
    {
        $properties = $hub->properties($request->validated('name'));

        abort_if($properties === null, 404);

        return response()->json($properties);
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
    public function updateFromPlugin(Request $request, SelectResourcePack $selector): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9_-]+$/'],
        ]);

        $user = $request->user();
        $installed = $selector->installAndSelect($user, $validated['name']);

        return response()->json([
            'resource_pack_id' => $user->resource_pack_id,
            'effective_resource_pack_id' => $user->effectiveResourcePackId(),
            'installed' => $installed,
            'queued' => ! $installed,
        ], $installed ? 200 : 202);
    }
}
