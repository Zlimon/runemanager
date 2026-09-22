<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserResourcePackController;
use App\Http\Requests\HubPackNameRequest;
use App\Jobs\FetchResourcePackJob;
use App\Models\ResourcePack;
use App\Services\ResourcePacks\DeleteResourcePack;
use App\Services\ResourcePacks\InstallResourcePack;
use App\Services\ResourcePacks\ResourcePackHub;
use App\Support\Instance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;

class ResourcePackController extends Controller
{
    /**
     * Browse the resource-pack hub and install packs.
     */
    public function index(ResourcePackHub $hub): Response
    {
        $defaultId = Instance::defaultResourcePackId();

        $installed = ResourcePack::query()
            ->with('installedBy:id,name')
            ->withCount('users')
            ->orderBy('alias')
            ->get()
            ->map(fn (ResourcePack $pack): array => [
                'id' => $pack->id,
                'name' => $pack->name,
                'alias' => $pack->alias,
                'icon_url' => $pack->icon_url,
                'installed_by' => $pack->installedBy?->name,
                'users_count' => $pack->users_count,
                'is_default' => $pack->id === $defaultId,
                'is_vanilla' => $pack->isVanilla(),
            ])
            ->all();

        return Inertia::render('Admin/Packs/Index', [
            'installed' => $installed,
            'hubPacks' => $hub->available(),
            'defaultId' => $defaultId,
            'vanillaMissing' => ! File::isDirectory(public_path('resource-packs/'.InstallResourcePack::VANILLA_PACK)),
        ]);
    }

    /**
     * Re-download the bundled "Vanilla" pack from upstream. It's the
     * baseline every other pack borrows missing sprites from, so it can't be
     * deleted via the UI — but if its on-disk assets go missing this restores
     * them (the DB row keeps its "Vanilla" identity, see upsertRow()).
     */
    public function reinstallVanilla(InstallResourcePack $installer): RedirectResponse
    {
        try {
            $pack = $installer->install(InstallResourcePack::VANILLA_PACK);
        } catch (\Throwable $e) {
            return back()->dangerBanner('Could not reinstall the Vanilla pack: '.$e->getMessage());
        }

        return back()->banner("Reinstalled {$pack->alias}.");
    }

    /**
     * Queue a hub install. Answers with JSON for the page's install modal (which
     * polls {@see UserResourcePackController::status()} until the assets land),
     * and falls back to a redirect for plain requests.
     */
    public function store(HubPackNameRequest $request, InstallResourcePack $installer): JsonResponse|RedirectResponse
    {
        $name = $request->validated('name');
        $installed = $installer->isReady($name);

        if (! $installed) {
            FetchResourcePackJob::dispatch($name);
        }

        if ($request->wantsJson()) {
            return response()->json(['name' => $name, 'installed' => $installed, 'queued' => ! $installed]);
        }

        return back()->with('status', "Installing {$name} — it'll appear shortly.");
    }

    /**
     * The owner can remove any installed pack (the bundled Default
     * Vanilla aside). Anyone using it falls back to the default; if it was the
     * instance default, that's cleared too (see {@see DeleteResourcePack}).
     */
    public function destroy(ResourcePack $pack, DeleteResourcePack $deleter): RedirectResponse
    {
        abort_if($pack->isVanilla(), 403, 'The bundled default pack cannot be deleted.');

        $alias = $pack->alias;
        $deleter->run($pack);

        return back()->with('status', "Deleted {$alias}.");
    }
}
