<?php

namespace App\Http\Middleware;

use App\Helpers\SettingHelper;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Models\Collection;
use App\Models\ResourcePack;
use App\Models\Skill;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Resource pack CSS is rendered server-side from app.blade.php as a <link>;
        // we forward `dark_mode` to drive Tailwind's `dark:` variants and `pack`
        // (name + updated_at timestamp) so Vue components can resolve pack-shipped
        // assets like /resource-packs/{name}/skill/{slug}.png with cache busting.
        $packId = $request->user()?->effectiveResourcePackId()
            ?? SettingHelper::getSetting('resource_pack_id');
        $pack = $packId ? ResourcePack::find($packId) : null;
        $darkMode = (bool) ($pack?->dark_mode);

        return array_merge(parent::share($request), [
            'app' => [
                'name' => config('app.name'),
                //                'url' => config('app.url'),
            ],
            'dark_mode' => $darkMode,
            'pack' => $pack ? [
                'name' => $pack->name,
                'version' => $pack->updated_at?->timestamp,
            ] : null,
            'skills' => fn () => Skill::distinct()->select('name', 'slug')->get()->toArray() ?? [],
            'bosses' => fn () => Collection::distinct()->select('name', 'slug')->where('category_id', 5)->get()->toArray() ?? [],
            'clues' => fn () => Collection::distinct()->select('name', 'slug')->where('category_id', 3)->get()->toArray() ?? [],

            // Header typeahead — only evaluated when a partial reload includes it
            // (router.reload({ only: ['accountSearchResults'], data: { account_search: ... } })).
            'accountSearchResults' => Inertia::optional(function () use ($request) {
                $query = trim((string) $request->query('account_search', ''));
                if ($query === '') {
                    return [];
                }

                return AccountResource::collection(
                    Account::query()
                        ->where('username', 'LIKE', '%'.$query.'%')
                        ->orderBy('username')
                        ->limit(10)
                        ->get(),
                )->resolve();
            }),
        ]);
    }
}
