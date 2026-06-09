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

        $query = Account::query();

        if (! empty($validated['username'] ?? null)) {
            // Case-insensitive substring match, portable across Postgres/SQLite
            // (Postgres LIKE is case-sensitive; ILIKE isn't available on SQLite).
            $query->whereRaw('LOWER(username) LIKE ?', ['%'.Str::lower($validated['username']).'%']);
        }

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

            'avatar' => fn () => $account->avatarPayload(),

            // SPEC §5.2 Loot — append-only history. Latest 25 drops with hydrated
            // item details for the recent-loot panel.
            'recentLoot' => fn () => LootResource::collectionWith($account->recentLoot(25))->resolve(),

            // External API — defer so the page paints first, then the client
            // pulls this in via a follow-up partial reload.
            'collectionLog' => Inertia::defer(fn () => $this->buildCollectionLog($account, $request)),

            // Per SPEC §5.3 — one timestamp per data type, plus the staleness
            // threshold the UI uses to flag old data.
            'freshness' => fn () => [
                'hiscores' => optional($account->hiscore)->fetched_at?->toIso8601String(),
                'inventory' => optional($account->inventory)->updated_at?->toIso8601String(),
                'bank' => optional($account->bank)->updated_at?->toIso8601String(),
                'looting_bag' => optional($account->lootingBag)->updated_at?->toIso8601String(),
                'quests' => optional($account->quests)->updated_at?->toIso8601String(),
                'diaries' => optional($account->diary)->updated_at?->toIso8601String(),
                'equipment' => optional($account->equipment)->updated_at?->toIso8601String(),
                'avatar' => $account->avatar_uploaded_at?->toIso8601String(),
                'loot' => $account->latestLootKilledAt()?->toIso8601String(),
                'stale_after_minutes' => (int) config('runemanager.freshness.stale_after_minutes'),
            ],
        ]);
    }

    /**
     * SPEC §5.2 — build the collection log view from the stored TempleOSRS data.
     * TempleOSRS gives overall progress plus the obtained items per category; we
     * list the categories and lazy-load one category's items at a time (selected
     * via ?ccollection) to keep the payload small.
     *
     * @return array<string, mixed>|null
     */
    private function buildCollectionLog(Account $account, Request $request): ?array
    {
        $log = $account->collectionLog;
        if ($log === null) {
            return null;
        }

        $items = $log->items ?? [];

        $categories = [];
        foreach ($items as $slug => $entries) {
            $categories[$slug] = [
                'slug' => $slug,
                'name' => $this->prettyCategoryName($slug),
                'obtained' => count($entries),
            ];
        }
        uasort($categories, fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

        $requested = $request->query('ccollection');
        $activeSlug = ($requested && isset($categories[$requested]))
            ? $requested
            : (array_key_first($categories) ?: null);

        $activeCollection = $activeSlug !== null
            ? $this->loadCollectionItems($categories[$activeSlug], $items[$activeSlug] ?? [])
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
     * Hydrate one category's obtained items with their icon/name details.
     *
     * @param  array<string, mixed>  $summary
     * @param  array<int, array<string, mixed>>  $entries
     * @return array<string, mixed>
     */
    private function loadCollectionItems(array $summary, array $entries): array
    {
        $items = Item::select('_id', 'name', 'examine', 'icon')
            ->whereIn('_id', array_column($entries, 'id'))
            ->get()
            ->keyBy('_id');

        $slots = [];
        foreach ($entries as $entry) {
            $dbItem = $items[$entry['id']] ?? null;
            $slots[] = [
                'id' => $entry['id'],
                'quantity' => $entry['count'] ?? 0,
                'date' => $entry['date'] ?? null,
                'item' => $dbItem ? $dbItem->toArray() : null,
            ];
        }

        return [
            'slug' => $summary['slug'],
            'name' => $summary['name'],
            'obtained' => $summary['obtained'],
            'items' => $slots,
        ];
    }

    private function prettyCategoryName(string $slug): string
    {
        return ucwords(str_replace('_', ' ', $slug));
    }
}
