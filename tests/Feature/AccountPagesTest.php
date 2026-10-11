<?php

use App\Helpers\SettingHelper;
use App\Models\Account;
use App\Models\FeedEvent;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    // The Vite dev server's SSR endpoint would count as a stray request.
    config(['inertia.ssr.enabled' => false]);
    $this->user = User::factory()->withPersonalTeam()->create();
    $this->account = Account::factory()->for($this->user)->create([
        'username' => 'Tabby',
        'world_x' => 3222,
        'world_y' => 3218,
        'world_plane' => 0,
        'location' => 'Lumbridge',
    ]);
});

it('keeps the account tab URLs and route names', function () {
    expect(route('accounts.show', $this->account, false))->toBe('/accounts/Tabby')
        ->and(route('accounts.gains', $this->account, false))->toBe('/accounts/Tabby/gains')
        ->and(route('accounts.items', $this->account, false))->toBe('/accounts/Tabby/items')
        ->and(route('accounts.collection', $this->account, false))->toBe('/accounts/Tabby/collection')
        ->and(route('accounts.events', $this->account, false))->toBe('/accounts/Tabby/events');
});

it('sends the shared header props on every tab', function (string $routeName) {
    SettingHelper::setSetting('wom_enabled', true, 'bool');

    $this->actingAs($this->user)
        ->get(route($routeName, $this->account))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('account.username', 'Tabby')
            ->has('account.equipment')
            ->where('position', ['x' => 3222, 'y' => 3218, 'plane' => 0])
            ->where('vitals', null)
            ->where('location', 'Lumbridge'),
        );
})->with(['accounts.show', 'accounts.gains', 'accounts.items', 'accounts.collection']);

it('renders the Log with its own props and no item, collection or activity props', function () {
    $this->actingAs($this->user)
        ->get(route('accounts.show', $this->account))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Accounts/Show')
            ->has('account.skills')
            ->has('account.bosses')
            ->has('account.clues')
            ->has('instanceRank')
            ->has('avatar')
            ->has('quests')
            ->has('diaries')
            ->has('combatAchievements')
            ->has('pinnedFeed')
            ->where('recentEvents', ['data' => [], 'next_cursor' => null])
            ->has('freshness', fn ($freshness) => $freshness
                ->hasAll(['hiscores', 'quests', 'diaries', 'combat_achievements', 'equipment', 'avatar', 'stale_after_minutes']))
            ->missing('feed')
            ->missing('inventory')
            ->missing('bank')
            ->missing('recentLoot')
            ->missing('collectionLog')
            ->missing('wom'),
        );
});

it('lists milestones in Recent Events but not routine events', function () {
    FeedEvent::create(['account_id' => $this->account->id, 'type' => FeedEvent::TYPE_PET, 'payload' => [], 'occurred_at' => now()->subHour()]);
    FeedEvent::create(['account_id' => $this->account->id, 'type' => FeedEvent::TYPE_LEVEL_UP, 'payload' => ['skill' => 'Hunter', 'level' => 41], 'occurred_at' => now()]);

    $this->actingAs($this->user)
        ->get(route('accounts.show', $this->account))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('recentEvents.data', 1)
            ->where('recentEvents.data.0.source', 'feed')
            ->where('recentEvents.data.0.type', 'pet')
            ->where('recentEvents.data.0.pinned', false)
            ->where('recentEvents.next_cursor', null),
        );
});

it('pages Recent Events as JSON through the cursor', function () {
    foreach (range(1, 17) as $minutes) {
        FeedEvent::create(['account_id' => $this->account->id, 'type' => FeedEvent::TYPE_PET, 'payload' => [], 'occurred_at' => now()->subMinutes($minutes)]);
    }

    $first = $this->actingAs($this->user)
        ->getJson(route('accounts.events', $this->account))
        ->assertOk()
        ->assertJsonCount(15, 'data')
        ->assertJsonStructure(['data' => [['id', 'source', 'type', 'title', 'body', 'occurred_at', 'approximate', 'icon']], 'next_cursor']);

    $cursor = $first->json('next_cursor');
    expect($cursor)->toBeString();

    $second = $this->actingAs($this->user)
        ->getJson(route('accounts.events', ['account' => $this->account, 'before' => $cursor]))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('next_cursor', null);

    $ids = [...$first->json('data.*.id'), ...$second->json('data.*.id')];
    expect(array_unique($ids))->toHaveCount(17);

    // A malformed cursor starts over rather than failing.
    $this->actingAs($this->user)
        ->getJson(route('accounts.events', ['account' => $this->account, 'before' => '!!!']))
        ->assertOk()
        ->assertJsonPath('data.0.id', $first->json('data.0.id'));
});

it('renders the Items tab with the inventory, looting bag and bank', function () {
    $this->actingAs($this->user)
        ->get(route('accounts.items', $this->account))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Accounts/Items/Show')
            ->where('inventory', null)
            ->where('lootingBag', null)
            ->where('bank', null)
            ->has('freshness', fn ($freshness) => $freshness
                ->hasAll(['inventory', 'looting_bag', 'bank', 'equipment', 'stale_after_minutes']))
            ->missing('quests')
            ->missing('recentEvents'),
        );
});

it('renders the Collection tab with recent loot and a deferred collection log', function () {
    $this->actingAs($this->user)
        ->get(route('accounts.collection', $this->account))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Accounts/Collection/Show')
            ->where('recentLoot', [])
            ->where('freshness.loot', null)
            ->has('freshness.stale_after_minutes')
            ->missing('collectionLog')
            ->missing('bank'),
        );
});

it('404s the Gains tab while Wise Old Man is off', function () {
    $this->actingAs($this->user)
        ->get(route('accounts.gains', $this->account))
        ->assertNotFound();
});
