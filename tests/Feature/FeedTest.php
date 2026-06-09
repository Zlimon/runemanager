<?php

use App\Events\FeedEventCreated;
use App\Models\Account;
use App\Models\AccountHiscore;
use App\Models\FeedEvent;
use App\Models\Item;
use App\Models\Quest;
use App\Models\User;
use App\Services\Feed\RecordFeedEvent;
use App\Services\Hiscores\HiscoresSync;
use App\Services\Hiscores\OsrsHiscoresClient;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
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

it('records a LEVEL_UP only when a configured threshold is crossed', function () {
    config()->set('runemanager.feed.level_up_thresholds', [50, 99]);

    $account = makeAccountForFeed();
    $recorder = new RecordFeedEvent;

    // 48 → 49: no threshold crossed, no event.
    expect($recorder->recordLevelUps(
        $account,
        previous: ['attack' => ['level' => 48]],
        current: ['attack' => ['level' => 49]],
    ))->toBe(0);

    // 48 → 50: crosses 50, one event.
    expect($recorder->recordLevelUps(
        $account,
        previous: ['attack' => ['level' => 48]],
        current: ['attack' => ['level' => 50]],
    ))->toBe(1);

    // 50 → 99: crosses both 50 and 99, but we collapse to the highest milestone.
    expect($recorder->recordLevelUps(
        $account,
        previous: ['defence' => ['level' => 50]],
        current: ['defence' => ['level' => 99]],
    ))->toBe(1);

    $events = FeedEvent::ofType(FeedEvent::TYPE_LEVEL_UP)->orderBy('id')->get();
    expect($events)->toHaveCount(2);
    expect($events[0]->payload['skill'])->toBe('attack');
    expect($events[0]->payload['milestone'])->toBe(50);
    expect($events[1]->payload['skill'])->toBe('defence');
    expect($events[1]->payload['milestone'])->toBe(99);
});

it('skips the "overall" pseudo-skill', function () {
    config()->set('runemanager.feed.level_up_thresholds', [50]);

    $account = makeAccountForFeed();
    $recorder = new RecordFeedEvent;

    expect($recorder->recordLevelUps(
        $account,
        previous: ['overall' => ['level' => 40]],
        current: ['overall' => ['level' => 60]],
    ))->toBe(0);

    expect(FeedEvent::count())->toBe(0);
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

it('HiscoresSync emits LEVEL_UP events through the recorder on a crossing sync', function () {
    config()->set('runemanager.feed.level_up_thresholds', [70]);

    $account = makeAccountForFeed();
    AccountHiscore::create([
        'account_id' => $account->id,
        'entries' => ['skills' => ['attack' => ['rank' => 1, 'level' => 69, 'xp' => 0]], 'activities' => []],
        'fetched_at' => now()->subHour(),
    ]);

    $mockedClient = new HttpClient([
        'handler' => HandlerStack::create(new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'skills' => [
                    ['id' => 0, 'name' => 'Overall', 'rank' => 1, 'level' => 100, 'xp' => 100],
                    ['id' => 1, 'name' => 'Attack', 'rank' => 1, 'level' => 70, 'xp' => 1000],
                ],
                'activities' => [],
            ])),
        ])),
    ]);

    $sync = new HiscoresSync(new OsrsHiscoresClient($mockedClient), new RecordFeedEvent);
    $sync->syncForAccount($account);

    expect(FeedEvent::ofType(FeedEvent::TYPE_LEVEL_UP)->count())->toBe(1);
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
