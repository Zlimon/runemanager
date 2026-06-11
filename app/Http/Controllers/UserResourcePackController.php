<?php

namespace App\Http\Controllers;

use App\Helpers\SettingHelper;
use App\Jobs\FetchResourcePackJob;
use App\Models\ResourcePack;
use App\Models\User;
use App\Services\ResourcePacks\DeleteResourcePack;
use App\Services\ResourcePacks\InstallResourcePack;
use App\Services\ResourcePacks\ResourcePackHub;
use App\Support\Roles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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
     * SPEC §6.2 — the user's appearance page: pick a personal override from the
     * installed packs, and browse the community hub to install new ones (so
     * members no longer depend on the RuneLite plugin to add a pack).
     */
    public function index(Request $request, InstallResourcePack $installer, ResourcePackHub $hub): Response
    {
        $installer->ensureVanilla();

        $user = $request->user();

        return Inertia::render('Themes/Index', [
            'packs' => ResourcePack::pickerList(),
            'hubPacks' => $hub->available(),
            'selectedId' => $user->resource_pack_id,
            'defaultId' => ((int) SettingHelper::getSetting('resource_pack_id', 0)) ?: null,
            'installLimit' => $this->installLimit(),
            'installedCount' => $this->userInstallCount($user),
            'canInstall' => $this->canInstallMore($user),
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
     * Website: install a pack from the community hub and apply it as the user's
     * personal theme. Same pipeline as the plugin push, keyed on a hub branch
     * name (`pack-*`). Answers with JSON so the Appearance page can show a
     * spinner and poll {@see status()} until the download lands.
     */
    public function install(Request $request, InstallResourcePack $installer): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^pack-[A-Za-z0-9_-]+$/'],
        ]);

        $user = $request->user();

        // Only a brand-new pack counts against the member's quota — re-adding one
        // already in the shared pool just points them at it.
        if (! ResourcePack::where('name', $validated['name'])->exists() && ! $this->canInstallMore($user)) {
            throw ValidationException::withMessages([
                'name' => "You've reached your limit of {$this->installLimit()} installed packs. Delete one to add another.",
            ]);
        }

        $installed = $this->installAndSelect($validated['name'], $user, $installer);

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
    public function status(Request $request, InstallResourcePack $installer): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^pack-[A-Za-z0-9_-]+$/'],
        ]);

        $name = $validated['name'];
        $pack = ResourcePack::where('name', $name)->first();

        return response()->json([
            'installed' => $pack !== null
                && $installer->isInstalled($name)
                && $pack->version !== 'pending',
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

        $user = $request->user();
        $installed = $this->installAndSelect($validated['name'], $user, $installer);

        return response()->json([
            'resource_pack_id' => $user->resource_pack_id,
            'effective_resource_pack_id' => $user->effectiveResourcePackId(),
            'installed' => $installed,
            'queued' => ! $installed,
        ], $installed ? 200 : 202);
    }

    /**
     * Ensure a ResourcePack row exists for $name, point the user's personal
     * override at it, and queue a fetch when the assets aren't installed yet.
     * Shared by the website install and the plugin push.
     *
     * Returns whether the pack is already fully installed — i.e. assets on disk
     * AND a populated DB row. A stub row (version=pending, null colours) — newly
     * created here or carried over from a migrate:fresh while the CSS survived on
     * disk — still needs the pipeline to fetch metadata and extract the palette.
     */
    private function installAndSelect(string $name, User $user, InstallResourcePack $installer): bool
    {
        $pack = ResourcePack::where('name', $name)->first();

        if (! $pack) {
            $pack = new ResourcePack;
            $pack->name = $name;
            $pack->alias = Str::title(str_replace(['pack-', '-'], ' ', $name));
            $pack->version = 'pending';
            $pack->author = 'pending';
            $pack->url = sprintf('https://github.com/melkypie/resource-packs/archive/%s.zip', $name);
            $pack->tags = '';
            $pack->dark_mode = false;
            $pack->installed_by_user_id = $user->id;
            $pack->save();
        }

        // Record the preference even before assets arrive — the Blade root falls
        // through to the default until they do.
        $user->resource_pack_id = $pack->id;
        $user->save();

        $installed = $installer->isInstalled($name) && $pack->version !== 'pending';

        if (! $installed) {
            FetchResourcePackJob::dispatch($name);
        }

        return $installed;
    }

    private function installLimit(): int
    {
        return (int) config('runemanager.resource_packs.user_install_limit', 5);
    }

    private function userInstallCount(User $user): int
    {
        return ResourcePack::where('installed_by_user_id', $user->id)->count();
    }

    /** The owner manages packs instance-wide, so the per-member quota doesn't apply. */
    private function canInstallMore(User $user): bool
    {
        return $user->can(Roles::MANAGE_INSTANCE)
            || $this->userInstallCount($user) < $this->installLimit();
    }
}
