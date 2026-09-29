<?php

use App\Events\FeedEventCreated;
use App\Models\Account;
use App\Models\FeedEvent;
use App\Models\Item;
use App\Models\Quest;
use App\Models\User;
use App\Services\Feed\RecordFeedEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Mongo collections persist across RefreshDatabase resets.
    Quest::query()->delete();
});

function makeAccountForFeed(string $username = 'Zlimon'): Account
{
    $user = User::query()->forceCreate([
        'name' => 'Feed Test',
        'email' => $username.'@feed.test',
        'password' => bcrypt('pw'),
        'icon_id' => 0,
    ]);

    return Account::query()->forceCreate([
        'user_id' => $user->id,
        'account_type' => 'normal',
        'username' => $username,
        'rank' => 0,
        'level' => 0,
        'xp' => 0,
    ]);
}

it('records a level-up pushed by the plugin (every level; UI filters milestones)', function () {
    $account = makeAccountForFeed('Leveller');
    Sanctum::actingAs($account->user);

    $this->postJson('/api/plugin/feed', ['type' => 'level_up', 'skill' => 'attack', 'level' => 73], [
        'Accept' => 'application/json',
        'X-Account-Hash' => 'lvl-hash',
        'X-Account-Username' => 'Leveller',
    ])->assertSuccessful();

    $event = FeedEvent::ofType(FeedEvent::TYPE_LEVEL_UP)->firstOrFail();
    expect($event->payload['skill'])->toBe('attack')
        ->and($event->payload['level'])->toBe(73);
});

it('requires skill and level for a level-up push', function () {
    $account = makeAccountForFeed('Leveller');
    Sanctum::actingAs($account->user);

    $this->postJson('/api/plugin/feed', ['type' => 'level_up'], [
        'Accept' => 'application/json',
        'X-Account-Hash' => 'lvl-hash',
        'X-Account-Username' => 'Leveller',
    ])->assertStatus(422)->assertJsonValidationErrors(['skill', 'level']);
});

it('broadcasts FeedEventCreated on the public feed channel when an event is recorded', function () {
    Event::fake([FeedEventCreated::class]);

    $account = makeAccountForFeed();
    (new RecordFeedEvent)->recordQuestCompletions($account, [], [['Cooks Assistant', 2]]);

    Event::assertDispatched(FeedEventCreated::class, function ($event) use ($account): bool {
        return $event->feedEvent->account_id === $account->id
            && $event->broadcastOn()[0]->name === 'feed'
            && $event->broadcastWith()['type'] === FeedEvent::TYPE_QUEST_COMPLETE;
    });
});

it('records a LOOT_DROP regardless of the admin minimum value (display filter only)', function () {
    config()->set('runemanager.feed.loot_min_value', 100_000);

    $account = makeAccountForFeed();
    $recorder = new RecordFeedEvent;

    $recorder->recordLootDrop($account, 'Cow', [['id' => 1739, 'quantity' => 1]], 50, now());
    $recorder->recordLootDrop($account, 'Vorkath', [['id' => 21907, 'quantity' => 1]], 250_000_000, now());

    expect(FeedEvent::ofType(FeedEvent::TYPE_LOOT_DROP)->count())->toBe(2);
});

it('stores the plugin-supplied position on feed events', function () {
    $account = makeAccountForFeed('Located');
    Sanctum::actingAs($account->user);
    $headers = ['Accept' => 'application/json', 'X-Account-Hash' => 'pos-hash', 'X-Account-Username' => 'Located'];

    $this->postJson('/api/plugin/feed', [
        'type' => 'level_up',
        'skill' => 'attack',
        'level' => 73,
        'position' => ['x' => 2662, 'y' => 3305, 'plane' => 0, 'location' => 'Ardougne'],
    ], $headers)->assertSuccessful();

    $this->postJson('/api/plugin/collection-log/unlock', [
        'item' => 'Bow string spool',
        'position' => ['x' => 3374, 'y' => 3893],
    ], $headers)->assertSuccessful();

    expect(FeedEvent::ofType(FeedEvent::TYPE_LEVEL_UP)->firstOrFail()->payload['position'])
        ->toBe(['x' => 2662, 'y' => 3305, 'plane' => 0, 'location' => 'Ardougne'])
        ->and(FeedEvent::ofType(FeedEvent::TYPE_COLLECTION_LOG)->firstOrFail()->payload['position'])
        ->toBe(['x' => 3374, 'y' => 3893, 'plane' => 0, 'location' => null]);
});

it('records events without a position when the plugin sends none', function () {
    $account = makeAccountForFeed('Nowhere');
    Sanctum::actingAs($account->user);

    $this->postJson('/api/plugin/feed', ['type' => 'pet'], [
        'Accept' => 'application/json', 'X-Account-Hash' => 'np-hash', 'X-Account-Username' => 'Nowhere',
    ])->assertSuccessful();

    expect(FeedEvent::ofType(FeedEvent::TYPE_PET)->firstOrFail()->payload)
        ->not->toHaveKey('position');
});

it('rejects a malformed position', function () {
    $account = makeAccountForFeed('Malformed');
    Sanctum::actingAs($account->user);

    $this->postJson('/api/plugin/feed', [
        'type' => 'pet',
        'position' => ['x' => 3200],
    ], [
        'Accept' => 'application/json', 'X-Account-Hash' => 'mf-hash', 'X-Account-Username' => 'Malformed',
    ])->assertStatus(422)->assertJsonValidationErrors('position.y');
});

it('attaches the quest push position to derived QUEST_COMPLETE events', function () {
    $account = makeAccountForFeed('Quester');
    Sanctum::actingAs($account->user);
    $headers = ['Accept' => 'application/json', 'X-Account-Hash' => 'q-hash', 'X-Account-Username' => 'Quester'];

    // First snapshot is a baseline — no events, even for finished quests.
    $this->putJson('/api/plugin/quests', [
        'quests' => [['Cooks Assistant', 2], ['Dragon Slayer II', 1]],
    ], $headers)->assertSuccessful();

    expect(FeedEvent::ofType(FeedEvent::TYPE_QUEST_COMPLETE)->count())->toBe(0);

    $this->putJson('/api/plugin/quests', [
        'quests' => [['Cooks Assistant', 2], ['Dragon Slayer II', 2]],
        'position' => ['x' => 3222, 'y' => 3218, 'plane' => 0, 'location' => 'Lumbridge'],
    ], $headers)->assertSuccessful();

    $event = FeedEvent::ofType(FeedEvent::TYPE_QUEST_COMPLETE)->firstOrFail();
    expect($event->payload['quest'])->toBe('Dragon Slayer II')
        ->and($event->payload['position']['location'])->toBe('Lumbridge');
});

it('records a QUEST_COMPLETE on the first push that flips a quest to finished', function () {
    $account = makeAccountForFeed();
    $recorder = new RecordFeedEvent;

    expect($recorder->recordQuestCompletions(
        $account,
        previous: [['Dragon Slayer II', 1]],
        current: [['Dragon Slayer II', 2]],
    ))->toBe(1);

    // Same payload on the next push (idempotent) — no duplicate event.
    expect($recorder->recordQuestCompletions(
        $account,
        previous: [['Dragon Slayer II', 2]],
        current: [['Dragon Slayer II', 2]],
    ))->toBe(0);

    expect(FeedEvent::ofType(FeedEvent::TYPE_QUEST_COMPLETE)->count())->toBe(1);
});

it('GET /feed renders the Inertia page and includes events in reverse-chronological order', function () {
    $account = makeAccountForFeed();

    FeedEvent::create([
        'account_id' => $account->id,
        'type' => FeedEvent::TYPE_QUEST_COMPLETE,
        'payload' => ['quest' => 'Earlier Quest'],
        'occurred_at' => now()->subHour(),
    ]);
    FeedEvent::create([
        'account_id' => $account->id,
        'type' => FeedEvent::TYPE_QUEST_COMPLETE,
        'payload' => ['quest' => 'Later Quest'],
        'occurred_at' => now()->subMinute(),
    ]);

    $this->get(route('feed.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Feed/Index')
            ->has('events', 2)
            ->where('events.0.payload.quest', 'Later Quest')
            ->where('events.1.payload.quest', 'Earlier Quest'),
        );
});

it('names loot-drop items on the feed', function () {
    $account = makeAccountForFeed();

    // Use a real item from the static collection so the name hydrates.
    $item = (new Item)->getConnection()->getDatabase()
        ->selectCollection((new Item)->getTable())
        ->findOne([], ['projection' => ['id' => 1, 'name' => 1, '_id' => 0]]);
    $itemId = (int) $item['id'];

    FeedEvent::create([
        'account_id' => $account->id,
        'type' => FeedEvent::TYPE_LOOT_DROP,
        'payload' => ['source' => 'Vorkath', 'items' => [['id' => $itemId, 'quantity' => 5]], 'total_value' => 1_000_000],
        'occurred_at' => now(),
    ]);

    $this->get(route('feed.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('events.0.type', 'loot_drop')
            ->where('events.0.payload.items.0.id', $itemId)
            ->where('events.0.payload.items.0.quantity', 5)
            ->where('events.0.payload.items.0.name', $item['name'])
            ->has('events.0.payload.items.0.icon'),
        );
});

it('exposes only this account\'s events on the account show page', function () {
    $account = makeAccountForFeed('Mine');
    $other = makeAccountForFeed('Theirs');

    FeedEvent::create(['account_id' => $account->id, 'type' => FeedEvent::TYPE_PET, 'payload' => [], 'occurred_at' => now()]);
    FeedEvent::create(['account_id' => $other->id, 'type' => FeedEvent::TYPE_PET, 'payload' => [], 'occurred_at' => now()]);

    $this->actingAs($account->user)
        ->get(route('accounts.show', $account))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            // The account page lists its milestones as Recent Events.
            ->has('recentEvents.data', 1)
            ->where('recentEvents.data.0.type', 'pet')
            ->where('recentEvents.data.0.source', 'feed'),
        );
});

it('lets the account owner delete their feed entry', function () {
    $account = makeAccountForFeed('Owner');
    $event = FeedEvent::create(['account_id' => $account->id, 'type' => FeedEvent::TYPE_PET, 'payload' => [], 'occurred_at' => now()]);

    $this->actingAs($account->user)
        ->delete(route('feed.destroy', $event))
        ->assertRedirect();

    expect(FeedEvent::find($event->id))->toBeNull();
});

it('lets an admin delete any feed entry', function () {
    $account = makeAccountForFeed('Someone');
    $event = FeedEvent::create(['account_id' => $account->id, 'type' => FeedEvent::TYPE_PET, 'payload' => [], 'occurred_at' => now()]);

    $this->actingAs(adminUser())
        ->delete(route('feed.destroy', $event))
        ->assertRedirect();

    expect(FeedEvent::find($event->id))->toBeNull();
});

it('forbids a non-owner non-admin from deleting a feed entry', function () {
    $account = makeAccountForFeed('Victim');
    $stranger = User::factory()->withPersonalTeam()->create();
    $event = FeedEvent::create(['account_id' => $account->id, 'type' => FeedEvent::TYPE_PET, 'payload' => [], 'occurred_at' => now()]);

    $this->actingAs($stranger)
        ->delete(route('feed.destroy', $event))
        ->assertForbidden();

    expect(FeedEvent::find($event->id))->not->toBeNull();
});

it('lets the owner pin and unpin a feed entry', function () {
    $account = makeAccountForFeed('Pinner');
    $event = FeedEvent::create(['account_id' => $account->id, 'type' => FeedEvent::TYPE_PET, 'payload' => [], 'occurred_at' => now()]);

    $this->actingAs($account->user)->put(route('feed.pin', $event))->assertRedirect();
    expect($event->refresh()->pinned_at)->not->toBeNull();

    $this->actingAs($account->user)->put(route('feed.pin', $event))->assertRedirect();
    expect($event->refresh()->pinned_at)->toBeNull();
});

it('forbids a non-owner (even an admin) from pinning a feed entry', function () {
    $account = makeAccountForFeed('Curated');
    $event = FeedEvent::create(['account_id' => $account->id, 'type' => FeedEvent::TYPE_PET, 'payload' => [], 'occurred_at' => now()]);

    $this->actingAs(adminUser())->put(route('feed.pin', $event))->assertForbidden();
    expect($event->refresh()->pinned_at)->toBeNull();
});

it('exposes pinned events as the account gallery', function () {
    $account = makeAccountForFeed('Galleried');
    FeedEvent::create(['account_id' => $account->id, 'type' => FeedEvent::TYPE_PET, 'payload' => [], 'occurred_at' => now(), 'pinned_at' => now()]);
    FeedEvent::create(['account_id' => $account->id, 'type' => FeedEvent::TYPE_DEATH, 'payload' => [], 'occurred_at' => now()]);

    $this->actingAs($account->user)
        ->get(route('accounts.show', $account))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('pinnedFeed', 1)
            ->where('pinnedFeed.0.type', 'pet')
            ->where('pinnedFeed.0.pinned', true),
        );
});

it('/feed is publicly accessible (no auth required)', function () {
    // No actingAs / Sanctum here on purpose — the feed is publicly visible.
    $this->get(route('feed.index'))->assertOk();
});

it('shares the admin display filters with the UI', function () {
    config()->set('runemanager.feed.loot_min_value', 100_000);

    $this->get(route('feed.index'))->assertInertia(
        fn (AssertableInertia $page) => $page
            ->where('feed_loot_min_value', 100_000)
            ->has('feed_level_milestones'),
    );
});

it('LootController honours the plugin feed flag (log-only entries emit no event)', function () {
    // The admin minimum only filters the Live Feed display, never storage.
    config()->set('runemanager.feed.loot_min_value', 100_000);

    $user = User::query()->forceCreate([
        'name' => 'Pusher',
        'email' => 'push@feed.test',
        'password' => bcrypt('pw'),
        'icon_id' => 0,
    ]);
    Sanctum::actingAs($user);

    $this->postJson('/api/plugin/loot', [
        'loot' => [
            // No flag — older plugin; treated as feed-worthy despite the admin floor.
            [
                'source' => 'Cow',
                'items' => [['id' => 1739, 'quantity' => 1]],
                'total_value' => 50,
                'killed_at' => '2026-06-02T17:30:00Z',
            ],
            // Below the plugin's own feed threshold — loot log only.
            [
                'source' => 'Man',
                'items' => [['id' => 995, 'quantity' => 15]],
                'total_value' => 15,
                'killed_at' => '2026-06-02T17:31:00Z',
                'feed' => false,
            ],
            [
                'source' => 'Vorkath',
                'items' => [['id' => 21907, 'quantity' => 1]],
                'total_value' => 250_000_000,
                'killed_at' => '2026-06-02T17:32:00Z',
                'feed' => true,
            ],
        ],
    ], [
        'Accept' => 'application/json',
        'X-Account-Hash' => 'feed-hash',
        'X-Account-Username' => 'Looter',
    ])->assertCreated()->assertJsonPath('data.inserted', 3);

    $sources = FeedEvent::ofType(FeedEvent::TYPE_LOOT_DROP)->pluck('payload')->pluck('source');
    expect($sources->all())->toEqualCanonicalizing(['Cow', 'Vorkath']);
});

it('records a COLLECTION_LOG event on a slot-unlock push', function () {
    $account = makeAccountForFeed('Collector');
    Sanctum::actingAs($account->user);

    $this->postJson('/api/plugin/collection-log/unlock', ['item' => 'Twisted bow'], [
        'Accept' => 'application/json',
        'X-Account-Hash' => 'cl-hash',
        'X-Account-Username' => 'Collector',
    ])->assertSuccessful();

    $event = FeedEvent::ofType(FeedEvent::TYPE_COLLECTION_LOG)->firstOrFail();
    expect($event->payload['item'])->toBe('Twisted bow')
        ->and($event->account_id)->toBe($account->id);
});

it('rejects a collection-log unlock without an item', function () {
    $account = makeAccountForFeed('Collector');
    Sanctum::actingAs($account->user);

    $this->postJson('/api/plugin/collection-log/unlock', [], [
        'Accept' => 'application/json',
        'X-Account-Hash' => 'cl-hash',
        'X-Account-Username' => 'Collector',
    ])->assertStatus(422)->assertJsonValidationErrors('item');
});

it('records pet, death and reward events from the generic feed endpoint', function () {
    $account = makeAccountForFeed('Notable');
    Sanctum::actingAs($account->user);
    $headers = ['Accept' => 'application/json', 'X-Account-Hash' => 'n-hash', 'X-Account-Username' => 'Notable'];

    $this->postJson('/api/plugin/feed', ['type' => 'pet'], $headers)->assertSuccessful();
    $this->postJson('/api/plugin/feed', ['type' => 'death'], $headers)->assertSuccessful();
    $this->postJson('/api/plugin/feed', ['type' => 'reward', 'source' => 'Barrows'], $headers)->assertSuccessful();

    expect(FeedEvent::ofType('pet')->count())->toBe(1)
        ->and(FeedEvent::ofType('death')->count())->toBe(1);
    $reward = FeedEvent::ofType('reward')->firstOrFail();
    expect($reward->payload['source'])->toBe('Barrows');
});

it('rejects a generic feed event with an unsupported type', function () {
    $account = makeAccountForFeed('Notable');
    Sanctum::actingAs($account->user);

    $this->postJson('/api/plugin/feed', ['type' => 'loot_drop'], [
        'Accept' => 'application/json', 'X-Account-Hash' => 'n-hash', 'X-Account-Username' => 'Notable',
    ])->assertStatus(422)->assertJsonValidationErrors('type');
});
