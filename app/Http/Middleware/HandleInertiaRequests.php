<?php

namespace App\Http\Middleware;

use App\Helpers\SettingHelper;
use App\Http\Resources\AccountResource;
use App\Models\Account;
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

        // A pack (user or instance-global) carries its own dark_mode and wins;
        // only when no pack is in effect does the user's own preference apply,
        // and only then may they toggle it.
        $darkMode = $pack ? (bool) $pack->dark_mode : (bool) $request->user()?->dark_mode;

        return array_merge(parent::share($request), [
            'app' => [
                'name' => config('app.name'),
                //                'url' => config('app.url'),
            ],
            'dark_mode' => $darkMode,
            'can_toggle_dark_mode' => $request->user() !== null && $pack === null,
            'pack' => $pack ? [
                'name' => $pack->name,
                'version' => $pack->updated_at?->timestamp,
            ] : null,
            'skills' => fn () => Skill::distinct()->select('name', 'slug')->get()->toArray() ?? [],

            // Boss list for the Hiscores nav — derived from a representative
            // account's hiscore activities (the OSRS API returns the full set on
            // every account), reusing the Account boss filter. Empty until at
            // least one account has synced.
            'bosses' => function () {
                $account = Account::query()->whereHas('hiscore')->with('hiscore')->first();

                return $account
                    ? $account->bosses->map(fn ($boss) => ['name' => $boss['name'], 'slug' => $boss['slug']])->all()
                    : [];
            },

            // Clues are the fixed seven tiers regardless of synced data.
            'clues' => fn () => collect(['beginner', 'easy', 'medium', 'hard', 'elite', 'master', 'all'])
                ->map(fn (string $tier) => ['name' => ucfirst($tier), 'slug' => "clue_scrolls_{$tier}"])
                ->all(),

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
