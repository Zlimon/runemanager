<?php

namespace App\Http\Controllers;

use App\Enums\AccountTypesEnum;
use App\Http\Resources\AccountResource;
use App\Http\Resources\BankResource;
use App\Http\Resources\FeedEventResource;
use App\Http\Resources\InventoryResource;
use App\Http\Resources\LootingBagResource;
use App\Http\Resources\LootResource;
use App\Models\Account;
use App\Models\FeedEvent;
use App\Rules\AccountUsernameRule;
use App\Services\CollectionLog\CollectionLogView;
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

    public function show(Request $request, Account $account, CollectionLogView $collectionLogView): Response
    {
        $account->load('equipment', 'hiscore')->append(['skills', 'bosses', 'clues']);

        return Inertia::render('Accounts/Show', [
            'account' => (new AccountResource($account))->resolve(),
            // Kept off AccountResource: that also serves the account lists, where
            // a count query per account would be one query per row.
            'instanceRank' => $account->instanceRank(),

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

            // Achievement Diary completion ({area: {tier: bool}}).
            'diaries' => fn () => $account->diary?->diaries ?? [],

            // Combat Achievement points + per-tier status, or
            // null until the plugin has pushed a snapshot.
            'combatAchievements' => fn () => $account->combatAchievement
                ? ['points' => $account->combatAchievement->points, 'tiers' => $account->combatAchievement->tiers]
                : null,

            'avatar' => fn () => $account->avatarPayload(),

            // Loot — append-only history. Latest 25 drops with hydrated
            // item details for the recent-loot panel.
            'recentLoot' => fn () => LootResource::collectionWith($account->recentLoot(25))->resolve(),

            // This account's slice of the live feed.
            'feed' => fn () => FeedEventResource::collectionWith(
                FeedEvent::query()->with('account:id,user_id,username,account_type')
                    ->where('account_id', $account->id)->recent(20)->get(),
            )->resolve(),

            // Owner-pinned events shown as the achievement gallery.
            'pinnedFeed' => fn () => FeedEventResource::collectionWith(
                FeedEvent::query()->with('account:id,user_id,username,account_type')
                    ->where('account_id', $account->id)->pinned()->get(),
            )->resolve(),

            // External API — defer so the page paints first, then the client
            // pulls this in via a follow-up partial reload.
            'collectionLog' => Inertia::defer(fn () => $collectionLogView->build($account, $request->query('ccollection'))),

            // One timestamp per data type, plus the staleness
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
}
