<?php

namespace App\Http\Controllers;

use App\Enums\AccountTypesEnum;
use App\Http\Resources\AccountResource;
use App\Http\Resources\BankResource;
use App\Http\Resources\InventoryResource;
use App\Http\Resources\LootingBagResource;
use App\Http\Resources\LootResource;
use App\Models\Account;
use App\Models\Item;
use App\Rules\AccountUsernameRule;
use App\Services\CollectionLog\CollectionLogStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function index(Request $request): Response
    {
        $allAccountTypes = array_values(AccountTypesEnum::returnAllAccountTypes());

        $validated = $request->validate([
            'username' => ['nullable', 'string', new AccountUsernameRule],
            'account_types' => ['nullable', 'array'],
            'account_types.*' => ['string', 'in:'.implode(',', $allAccountTypes)],
            'per_page' => ['nullable', 'integer', 'min:4', 'max:64'],
        ]);

        $query = Account::query()->searchUsername($validated['username'] ?? null);

        if (! empty($validated['account_types'] ?? [])) {
            $normalized = array_map(
                fn (string $type): string => Str::replace([' ', '-'], '_', Str::lower($type)),
                $validated['account_types'],
            );
            $query->whereIn('account_type', $normalized);
        }

        $accounts = AccountResource::collection(
            $query->orderBy('username')->paginate($validated['per_page'] ?? 16)->withQueryString(),
        );

        return Inertia::render('Accounts/Index', [
            'accountTypes' => $allAccountTypes,
            'accounts' => $accounts,
            'filters' => [
                'username' => $validated['username'] ?? '',
                'account_types' => $validated['account_types'] ?? [],
                'per_page' => $validated['per_page'] ?? 16,
            ],
        ]);
    }

    public function show(Request $request, Account $account): Response
    {
        $account->load('equipment', 'hiscore')->append(['skills', 'bosses', 'clues']);

        return Inertia::render('Accounts/Show', [
            'account' => (new AccountResource($account))->resolve(),

            // Last known in-game position for the minimap; live updates arrive
            // over the shared map broadcast channel. Null when never shared.
            'position' => $account->world_x !== null
                ? ['x' => $account->world_x, 'y' => $account->world_y, 'plane' => $account->world_plane]
                : null,

            // Live status-orb values; updated over the account channel
            // (VitalsUpdated). Null until the plugin first pushes them.
            'vitals' => $account->vitalsPayload(),

            // Named area the character is in (for the minimap caption); updated
            // live over the account channel (StatusUpdated).
            'location' => $account->location,

            'inventory' => fn () => $account->inventory
                ? (new InventoryResource($account->inventory))->resolve()
                : null,

            'bank' => fn () => $account->bank
                ? (new BankResource($account->bank))->resolve()
                : null,

            'lootingBag' => fn () => $account->lootingBag
                ? (new LootingBagResource($account->lootingBag))->resolve()
                : null,

            'quests' => fn () => $account->quests,

            // SPEC §5.2 — Achievement Diary completion ({area: {tier: bool}}).
            'diaries' => fn () => $account->diary?->diaries ?? [],

            // SPEC §5.2/§7.1 — Combat Achievement points + per-tier status, or
            // null until the plugin has pushed a snapshot.
            'combatAchievements' => fn () => $account->combatAchievement
                ? ['points' => $account->combatAchievement->points, 'tiers' => $account->combatAchievement->tiers]
                : null,

            'avatar' => fn () => $account->avatarPayload(),

            // SPEC §5.2 Loot — append-only history. Latest 25 drops with hydrated
            // item details for the recent-loot panel.
            'recentLoot' => fn () => LootResource::collectionWith($account->recentLoot(25))->resolve(),

            // External API — defer so the page paints first, then the client
            // pulls this in via a follow-up partial reload.
            'collectionLog' => Inertia::defer(fn () => $this->buildCollectionLog($account, $request, app(CollectionLogStructure::class))),

            // Per SPEC §5.3 — one timestamp per data type, plus the staleness
            // threshold the UI uses to flag old data.
            'freshness' => fn () => [
                'hiscores' => optional($account->hiscore)->fetched_at?->toIso8601String(),
                'inventory' => optional($account->inventory)->updated_at?->toIso8601String(),
                'bank' => optional($account->bank)->updated_at?->toIso8601String(),
                'looting_bag' => optional($account->lootingBag)->updated_at?->toIso8601String(),
                'quests' => optional($account->quests)->updated_at?->toIso8601String(),
                'diaries' => optional($account->diary)->updated_at?->toIso8601String(),
                'combat_achievements' => optional($account->combatAchievement)->updated_at?->toIso8601String(),
                'equipment' => optional($account->equipment)->updated_at?->toIso8601String(),
                'avatar' => $account->avatar_uploaded_at?->toIso8601String(),
                'loot' => $account->latestLootKilledAt()?->toIso8601String(),
                'stale_after_minutes' => (int) config('runemanager.freshness.stale_after_minutes'),
            ],
        ]);
    }

    /**
     * SPEC §5.2 — build the collection log view from the stored TempleOSRS data.
     * The player sync only returns *obtained* items per category, so we overlay
     * it on the full TempleOSRS structure (every category's complete item list,
     * grouped) to show missing items and real per-category totals — mirroring the
     * TempleOSRS UI. One category's items are loaded at a time (via ?ccollection)
     * to keep the payload small.
     *
     * @return array<string, mixed>|null
     */
    private function buildCollectionLog(Account $account, Request $request, CollectionLogStructure $structure): ?array
    {
        $log = $account->collectionLog;
        if ($log === null) {
            return null;
        }

        $obtainedItems = $log->items ?? [];
        $flat = $structure->flatten();
        // Fall back to the obtained-only set if the structure can't be fetched, so
        // the panel still works offline (no missing items / real totals then).
        $catIds = $flat['ids'] ?: array_map(
            fn (array $entries): array => array_map(fn (array $e): int => (int) $e['id'], $entries),
            $obtainedItems,
        );

        $categories = [];
        foreach ($catIds as $slug => $ids) {
            $obtainedCount = count(array_intersect($ids, array_column($obtainedItems[$slug] ?? [], 'id')));
            $categories[$slug] = [
                'slug' => $slug,
                'name' => $this->prettyCategoryName($slug),
                'group' => $flat['group'][$slug] ?? 'other',
                'obtained' => $obtainedCount,
                'total' => count($ids),
            ];
        }
        uasort($categories, fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

        $requested = $request->query('ccollection');
        $activeSlug = ($requested && isset($categories[$requested]))
            ? $requested
            : (array_key_first($categories) ?: null);

        $activeCollection = $activeSlug !== null
            ? $this->loadCollectionItems($categories[$activeSlug], $catIds[$activeSlug], $obtainedItems[$activeSlug] ?? [])
            : null;

        return [
            'categories' => array_values($categories),
            'activeSlug' => $activeSlug,
            'activeCollection' => $activeCollection,
            'obtained' => (int) $log->obtained,
            'total' => (int) $log->total,
            'fetchedAt' => $log->fetched_at?->toIso8601String(),
        ];
    }

    /**
     * Hydrate one category's full ordered item list with icon/name/examine, marking
     * which the player has obtained (with quantity + date) and which are missing.
     *
     * @param  array<string, mixed>  $summary
     * @param  list<int>  $orderedIds  the category's complete item list, in display order
     * @param  array<int, array<string, mixed>>  $obtained  the player's obtained entries
     * @return array<string, mixed>
     */
    private function loadCollectionItems(array $summary, array $orderedIds, array $obtained): array
    {
        // Items store the OSRS id in the `id` field (Mongo `_id` is an ObjectId),
        // so reuse the shared lookup the inventory/bank/loot panels use.
        $details = Item::lookupByOsrsIds($orderedIds);
        $obtainedById = collect($obtained)->keyBy(fn (array $e): int => (int) $e['id']);

        $slots = array_map(function (int $id) use ($details, $obtainedById): array {
            $entry = $obtainedById->get($id);

            return [
                'id' => $id,
                'item' => $details[$id] ?? null,
                'quantity' => (int) ($entry['count'] ?? 0),
                'date' => $entry['date'] ?? null,
                'obtained' => $entry !== null,
            ];
        }, $orderedIds);

        return [
            'slug' => $summary['slug'],
            'name' => $summary['name'],
            'group' => $summary['group'],
            'obtained' => $summary['obtained'],
            'total' => $summary['total'],
            'items' => $slots,
        ];
    }

    private function prettyCategoryName(string $slug): string
    {
        return ucwords(str_replace('_', ' ', $slug));
    }
}
