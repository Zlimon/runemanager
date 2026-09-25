<?php

namespace App\Http\Middleware;

use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Models\ResourcePack;
use App\Models\Skill;
use App\Support\Instance;
use App\Support\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        // A pack is always in effect — Vanilla is the floor.
        $user = $request->user();
        $pack = ResourcePack::effectiveFor($user);

        // Dark mode resolves through Instance: the viewer's own toggle wins, then
        // a guest's cookie choice, then the owner's instance default, then the
        // pack's (unreliable) flag. Vanilla short-circuits all of it — it
        // ships one fixed scheme and hides the toggle.
        $darkMode = Instance::resolveDarkMode($user, $pack);

        return array_merge(parent::share($request), [
            'app' => [
                'name' => config('app.name'),
                //                'url' => config('app.url'),
            ],
            // Drives the admin-only nav entry. The Owner holds every
            // management permission; clan/group elevation comes later.
            'is_admin' => $user !== null && $user->can(Roles::MANAGE_INSTANCE),
            // The viewer's own OSRS accounts, for quick links in the nav dropdown.
            'my_accounts' => $user
                ? $user->account()->orderBy('username')->get(['id', 'username', 'account_type'])
                    ->map(fn ($account) => [
                        // `id` is what anything account-scoped must key on — usernames
                        // change (see UsernameHistory), ids do not.
                        'id' => $account->id,
                        'username' => $account->username,
                        'account_type' => $account->account_type?->value ?? $account->account_type,
                    ])->all()
                : [],
            // The viewer's own resource packs, for the quick switcher in the
            // same nav dropdown, plus the selection it marks as active. The
            // shared `pack` below is the pack in *effect*, which cannot stand in
            // for this: a member whose pick happens to match the instance
            // default is a different state from one following that default, and
            // the switcher has to tell them apart.
            'my_packs' => $user ? ResourcePack::navListFor($user) : [],
            'pack_preference' => $user ? [
                'id' => $user->resource_pack_id,
                'disabled' => (bool) $user->disable_resource_pack,
            ] : null,
            'instance' => [
                'mode' => Instance::mode(),
                'name' => Instance::name(),
                'description' => Instance::description(),
                'logo_url' => Instance::logoUrl(),
                'banner_url' => Instance::bannerUrl(),
            ],
            'dark_mode' => $darkMode,
            'can_toggle_dark_mode' => Instance::canToggleDarkMode($pack),
            // The DaisyUI theme stamped on <html data-theme>; forwarded so the
            // client can keep it in sync across SPA navigations without
            // re-deriving the precedence rules.
            'theme' => Instance::themeFor($pack, $darkMode),
            // Drives the guest-only appearance picker: guests need a way out of
            // Vanilla to reach the untextured, toggleable interface.
            'no_pack' => $pack === null,
            // Admin display filters for the Live Feed surfaces only.
            // Everything is stored; the account Activity feed shows it all.
            'feed_level_milestones' => Instance::feedLevelUpThresholds(),
            'feed_loot_min_value' => Instance::feedLootMinValue(),
            'pack' => $pack ? [
                'name' => $pack->name,
                'version' => $pack->updated_at?->timestamp,
            ] : null,
            // Both nav lists are near-static — the skills only change with a
            // migration, the bosses only when Jagex adds one — and both are
            // plain closures, which Inertia evaluates on every single response.
            // Cached rather than re-queried. Both entries are dropped by the
            // deploy's cache:clear and by ResetInstanceData, so a migration or
            // a mode switch shows up at once rather than when the TTL expires.
            'skills' => fn (): array => Cache::remember(
                'nav.skills',
                now()->addDay(),
                fn (): array => Skill::distinct()->select('name', 'slug', 'order')->ordered()->get()->toArray(),
            ),

            'bosses' => function (): array {
                // Deliberately not cached while empty: a fresh instance would
                // otherwise keep an empty Hiscores nav until the entry expired
                // instead of until its first account synced.
                $bosses = Cache::get('nav.bosses');

                if ($bosses === null) {
                    $bosses = $this->bossNav();

                    if ($bosses !== []) {
                        Cache::put('nav.bosses', $bosses, now()->addDay());
                    }
                }

                return $bosses;
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

                // Same case-insensitive username search as the account index.
                return AccountResource::collection(
                    Account::query()
                        ->with('user')
                        ->searchUsername($query)
                        ->orderBy('username')
                        ->limit(10)
                        ->get(),
                )->resolve();
            }),
        ]);
    }

    /**
     * Boss list for the Hiscores nav — derived from a representative account's
     * hiscore activities (the OSRS API returns the full set on every account),
     * reusing the Account boss filter. Empty until at least one account synced.
     *
     * @return array<int, array{name: string, slug: string}>
     */
    private function bossNav(): array
    {
        $account = Account::query()->whereHas('hiscore')->with('hiscore')->first();

        return $account
            ? $account->bosses->map(fn ($boss) => ['name' => $boss['name'], 'slug' => $boss['slug']])->all()
            : [];
    }
}
