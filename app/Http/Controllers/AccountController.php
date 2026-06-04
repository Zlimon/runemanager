<?php

namespace App\Http\Controllers;

use App\Clients\CollectionLogClient;
use App\Enums\AccountTypesEnum;
use App\Http\Resources\AccountResource;
use App\Http\Resources\BankResource;
use App\Http\Resources\InventoryResource;
use App\Http\Resources\LootingBagResource;
use App\Http\Resources\LootResource;
use App\Models\Account;
use App\Models\Collection;
use App\Models\Item;
use App\Rules\AccountUsernameRule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

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
            $query->where('username', 'LIKE', '%'.$validated['username'].'%');
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
                'equipment' => optional($account->equipment)->updated_at?->toIso8601String(),
                'avatar' => $account->avatar_uploaded_at?->toIso8601String(),
                'loot' => $account->latestLootKilledAt()?->toIso8601String(),
                'stale_after_minutes' => (int) config('runemanager.freshness.stale_after_minutes'),
            ],
        ]);
    }

    /**
     * @return array{tabs: array<string, mixed>, activeTab: ?string, activeCollection: ?array<string, mixed>}|null
     */
    private function buildCollectionLog(Account $account, Request $request): ?array
    {
        try {
            $client = new CollectionLogClient;
            $response = $client->request('GET', "/collectionlog/user/{$account->username}");
            $payload = json_decode($response->getBody()->getContents(), true);
        } catch (Throwable $e) {
            report($e);

            return null;
        }

        $allowedTabs = ['Bosses', 'Raids', 'Clues'];
        $tabs = [];

        foreach ($payload['collectionLog']['tabs'] ?? [] as $tabName => $tab) {
            if (! in_array($tabName, $allowedTabs, true)) {
                continue;
            }

            foreach ($tab as $collectionName => $collection) {
                $slug = Str::slug($collectionName);
                $obtained = count(array_filter(
                    $collection['items'] ?? [],
                    fn ($item) => ($item['obtained'] ?? false) === true,
                ));

                $tabs[$tabName][$slug] = [
                    'name' => $collectionName,
                    'slug' => $slug,
                    'obtained' => $obtained,
                    'total' => count($collection['items'] ?? []),
                ];
            }
        }

        $requestedTab = $request->query('ctab');
        $requestedCollection = $request->query('ccollection');

        $activeTab = ($requestedTab && isset($tabs[$requestedTab]))
            ? $requestedTab
            : (array_key_first($tabs) ?: null);

        $activeCollection = null;
        if ($activeTab !== null) {
            $availableSlugs = array_keys($tabs[$activeTab] ?? []);
            $slug = ($requestedCollection && in_array($requestedCollection, $availableSlugs, true))
                ? $requestedCollection
                : ($availableSlugs[0] ?? null);

            if ($slug !== null) {
                $activeCollection = $this->loadCollectionItems($account, $activeTab, $slug, $tabs[$activeTab][$slug]);
            }
        }

        return [
            'tabs' => $tabs,
            'activeTab' => $activeTab,
            'activeCollection' => $activeCollection,
        ];
    }

    /**
     * @param  array<string, mixed>  $summary
     * @return array<string, mixed>|null
     */
    private function loadCollectionItems(Account $account, string $tab, string $slug, array $summary): ?array
    {
        $collection = Collection::whereSlug($slug)->first();
        if (! $collection) {
            return null;
        }

        try {
            $client = new CollectionLogClient;
            $response = $client->request('GET', "/items/user/{$account->username}?pageName={$collection->name}");
            $payload = json_decode($response->getBody()->getContents(), true);
        } catch (Throwable $e) {
            report($e);

            return null;
        }

        $items = Item::select('_id', 'name', 'examine', 'icon')
            ->whereIn('_id', array_column($payload['items'] ?? [], 'id'))
            ->get()
            ->keyBy('_id');

        $slots = [];
        foreach (($payload['items'] ?? []) as $item) {
            $dbItem = $items[$item['id']] ?? null;
            $slots[] = [
                'id' => $item['id'],
                'quantity' => $item['quantity'] ?? 0,
                'obtained' => $item['obtained'] ?? false,
                'item' => $dbItem ? $dbItem->toArray() : null,
            ];
        }

        return [
            'name' => $collection->name,
            'slug' => $collection->slug,
            'tab' => $tab,
            'obtained' => $payload['obtainedCount'] ?? $summary['obtained'],
            'total' => $payload['itemCount'] ?? $summary['total'],
            'killCount' => $payload['killCount'] ?? [],
            'items' => $slots,
        ];
    }
}
