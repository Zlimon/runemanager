<?php

namespace App\Http\Controllers;

use App\Helpers\SettingHelper;
use App\Http\Controllers\Concerns\LimitsResourcePackInstalls;
use App\Models\ResourcePack;
use App\Services\ResourcePacks\InstallResourcePack;
use App\Services\ResourcePacks\ResourcePackHub;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ThemeController extends Controller
{
    use LimitsResourcePackInstalls;

    /**
     * The user's appearance page: pick a personal override from the
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
            'noPack' => $user->disable_resource_pack,
            'defaultId' => ((int) SettingHelper::getSetting('resource_pack_id', 0)) ?: null,
            'installLimit' => $this->installLimit(),
            'installedCount' => $this->userInstallCount($user),
            'canInstall' => $this->canInstallMore($user),
        ]);
    }
}
