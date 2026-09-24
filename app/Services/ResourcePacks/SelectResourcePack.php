<?php

namespace App\Services\ResourcePacks;

use App\Jobs\FetchResourcePackJob;
use App\Models\ResourcePack;
use App\Models\User;
use App\Support\Instance;
use Illuminate\Support\Str;

/**
 * Point a member's personal appearance at a resource pack, installing it from
 * the hub first when the instance does not have it yet. Shared by the website
 * picker and install endpoint and the plugin's in-game pack push. This is the
 * user's own preference only; the instance default is managed elsewhere.
 */
class SelectResourcePack
{
    public function __construct(private InstallResourcePack $installer) {}

    /**
     * Point the user's appearance at a new pack selection, and let the newly
     * selected pack's own light/dark flag govern again.
     *
     * Picking a pack *is* choosing an appearance. Left alone, a toggle stored
     * while a previous pack was active keeps winning forever
     * ({@see Instance::resolveDarkMode()} puts the user's own flag at the top),
     * so a pack drawn for dark chrome lands under the light theme and reads as
     * unreadable. Rather than adding a second source of truth that fights that
     * precedence, the now-stale explicit choice is dropped: resolution falls
     * through to its next rungs — the owner's instance default, then the pack's
     * flag — exactly as it does for a member who never touched the toggle. The
     * toggle still works, and still wins, until the next pack change.
     *
     * Only a genuine change clears it, so re-saving the pack already in effect —
     * or the plugin re-pushing the in-game pack on every login — leaves a
     * deliberate preference alone.
     */
    public function select(User $user, ?int $packId, bool $disabled): void
    {
        $changed = $user->resource_pack_id !== $packId
            || (bool) $user->disable_resource_pack !== $disabled;

        $user->resource_pack_id = $packId;
        $user->disable_resource_pack = $disabled;

        if ($changed) {
            $user->dark_mode = null;
        }

        $user->save();
    }

    /**
     * Ensure a ResourcePack row exists for $name, point the user's personal
     * override at it, and queue a fetch when the pack is not ready yet.
     *
     * Returns whether the pack is already fully installed
     * ({@see InstallResourcePack::isReady()}).
     */
    public function installAndSelect(User $user, string $name): bool
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
        // through to the default until they do. Applying a pack re-enables textures.
        $this->select($user, $pack->id, false);

        $installed = $this->installer->isReady($name);

        if (! $installed) {
            FetchResourcePackJob::dispatch($name);
        }

        return $installed;
    }
}
