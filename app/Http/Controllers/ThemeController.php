<?php

namespace App\Http\Controllers;

use App\Models\ResourcePack;
use App\Services\ResourcePacks\InstallResourcePack;
use App\Services\ResourcePacks\PackInstallQuota;
use App\Services\ResourcePacks\ResourcePackHub;
use App\Support\Instance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ThemeController extends Controller
{
    /**
     * The user's appearance page: pick a personal override from the
     * installed packs, and browse the community hub to install new ones (so
     * members no longer depend on the RuneLite plugin to add a pack).
     */
    public function index(Request $request, InstallResourcePack $installer, ResourcePackHub $hub, PackInstallQuota $quota): Response
    {
        $installer->ensureVanilla();

        $user = $request->user();

        return Inertia::render('Themes/Index', [
            'packs' => ResourcePack::pickerList(),
            'hubPacks' => $hub->available(),
            'selectedId' => $user->resource_pack_id,
            'noPack' => $user->disable_resource_pack,
            'defaultId' => Instance::defaultResourcePackId(),
            'installLimit' => $quota->limit(),
            'installedCount' => $quota->countFor($user),
            'canInstall' => $quota->allows($user),
        ]);
    }
}
