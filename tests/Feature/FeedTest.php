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
    (new RecordFeedEvent)->recordQuestCompletions($account, [], [['Cooks Assistant', 901389]]);

    Event::assertDispatched(FeedEventCreated::class, function ($event) use ($account): bool {
        return $event->feedEvent->account_id === $account->id
            && $event->broadcastOn()[0]->name === 'feed'
            && $event->broadcastWith()['type'] === FeedEvent::TYPE_QUEST_COMPLETE;
    });
});

it('records a LOOT_DROP only when total_value clears the configured floor', function () {
    config()->set('runemanager.feed.loot_min_value', 100_000);

    $account = makeAccountForFeed();
    $recorder = new RecordFeedEvent;

    expect($recorder->recordLootDrop($account, 'Cow', [['id' => 1739, 'quantity' => 1]], 50, now()))
        ->toBeFalse();

    expect($recorder->recordLootDrop($account, 'Vorkath', [['id' => 21907, 'quantity' => 1]], 250_000_000, now()))
        ->toBeTrue();

    expect(FeedEvent::ofType(FeedEvent::TYPE_LOOT_DROP)->count())->toBe(1);
});

it('records a QUEST_COMPLETE on the first push that flips a quest to finished', function () {
    $account = makeAccountForFeed();
    $recorder = new RecordFeedEvent;

    expect($recorder->recordQuestCompletions(
        $account,
        previous: [['Dragon Slayer II', 1]],
        current: [['Dragon Slayer II', 901389]],
    ))->toBe(1);

    // Same payload on the next push (idempotent) — no duplicate event.
    expect($recorder->recordQuestCompletions(
        $account,
        previous: [['Dragon Slayer II', 901389]],
        current: [['Dragon Slayer II', 901389]],
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
            ->has('feed', 1)
            ->where('feed.0.type', 'pet')
            ->where('feed.0.account.username', 'Mine'),
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

it('/feed is publicly accessible (no auth required)', function () {
    // No actingAs / Sanctum here on purpose — SPEC §8.2: "publicly visible".
    $this->get(route('feed.index'))->assertOk();
});

it('LootController emits a feed event on a qualifying push', function () {
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
            [
                'source' => 'Vorkath',
                'items' => [['id' => 21907, 'quantity' => 1]],
                'total_value' => 250_000_000,
                'killed_at' => '2026-06-02T17:30:00Z',
            ],
            // Below the floor — should not generate a feed event.
            [
                'source' => 'Cow',
                'items' => [['id' => 1739, 'quantity' => 1]],
                'total_value' => 50,
                'killed_at' => '2026-06-02T17:31:00Z',
            ],
        ],
    ], [
        'Accept' => 'application/json',
        'X-Account-Hash' => 'feed-hash',
        'X-Account-Username' => 'Looter',
    ])->assertCreated();

    expect(FeedEvent::ofType(FeedEvent::TYPE_LOOT_DROP)->count())->toBe(1);
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
