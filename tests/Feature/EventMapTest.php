<?php

use App\Events\FeedEventCreated;
use App\Helpers\SettingHelper;
use App\Http\Resources\RecentEventResource;
use App\Models\Account;
use App\Models\FeedEvent;
use App\Models\Item;
use App\Models\User;
use App\Services\Accounts\RecentEvents;
use App\Services\Feed\RecordFeedEvent;
use App\Services\Map\EventMap;
use App\Support\Instance;
use App\Support\Roles;
use Database\Seeders\DemoEventPositionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Testing\AssertableInertia;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Contracts\DeletesUsers;
use Laravel\Jetstream\Jetstream;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['inertia.ssr.enabled' => false]);
    $this->travelTo(Carbon::parse('2026-09-28 12:00:00', 'UTC'));

    $this->viewer = User::factory()->withPersonalTeam()->create();
    $this->own = Account::factory()->for($this->viewer)->create(['username' => 'Mine']);

    $this->other = User::factory()->withPersonalTeam()->create();
    $this->shared = Account::factory()->for($this->other)->create(['username' => 'Shared', 'show_events_on_map' => true]);
    $this->hidden = Account::factory()->for($this->other)->create(['username' => 'Hidden']);
});

/**
 * @param  array<string, mixed>  $attributes
 */
function eventAt(Account $account, int $x, int $y, int $plane = 0, array $attributes = []): FeedEvent
{
    return FeedEvent::factory()->for($account)->questComplete()->create([
        'payload' => ['quest' => 'Dragon Slayer II', 'position' => ['x' => $x, 'y' => $y, 'plane' => $plane, 'location' => 'Somewhere']],
        ...$attributes,
    ]);
}

it('fills the position columns from the payload on save', function () {
    $event = eventAt($this->own, 3222, 3218, 1)->fresh();

    expect($event->world_x)->toBe(3222)
        ->and($event->world_y)->toBe(3218)
        ->and($event->world_plane)->toBe(1)
        ->and($event->location)->toBe('Somewhere');

    $event->update(['payload' => ['quest' => 'Dragon Slayer II']]);

    expect($event->fresh()->world_x)->toBeNull()
        ->and($event->fresh()->location)->toBeNull();
});

it('reads positions defensively', function (array $payload, array $expected) {
    expect(FeedEvent::position($payload))->toBe($expected);
})->with([
    'none' => [[], ['world_x' => null, 'world_y' => null, 'world_plane' => null, 'location' => null]],
    'no y' => [['position' => ['x' => 1]], ['world_x' => null, 'world_y' => null, 'world_plane' => null, 'location' => null]],
    'no plane, blank location' => [['position' => ['x' => 1, 'y' => 2, 'location' => ' ']], ['world_x' => 1, 'world_y' => 2, 'world_plane' => 0, 'location' => null]],
    'long location' => [['position' => ['x' => 1, 'y' => 2, 'plane' => 3, 'location' => str_repeat('a', 100)]], ['world_x' => 1, 'world_y' => 2, 'world_plane' => 3, 'location' => str_repeat('a', 80)]],
    'integer strings' => [['position' => ['x' => '3222', 'y' => '3218', 'plane' => '1']], ['world_x' => 3222, 'world_y' => 3218, 'world_plane' => 1, 'location' => null]],
    'upper bound' => [['position' => ['x' => 32767, 'y' => 0]], ['world_x' => 32767, 'world_y' => 0, 'world_plane' => 0, 'location' => null]],
    'huge' => [['position' => ['x' => 3_000_000_000, 'y' => 3218]], ['world_x' => null, 'world_y' => null, 'world_plane' => null, 'location' => null]],
    'just over' => [['position' => ['x' => 3222, 'y' => 32768]], ['world_x' => null, 'world_y' => null, 'world_plane' => null, 'location' => null]],
    'negative' => [['position' => ['x' => -1, 'y' => 3218]], ['world_x' => null, 'world_y' => null, 'world_plane' => null, 'location' => null]],
    'float string' => [['position' => ['x' => '3222.5', 'y' => 3218]], ['world_x' => null, 'world_y' => null, 'world_plane' => null, 'location' => null]],
    'float' => [['position' => ['x' => 3222.0, 'y' => 3218]], ['world_x' => null, 'world_y' => null, 'world_plane' => null, 'location' => null]],
    'non-numeric' => [['position' => ['x' => 'abc', 'y' => 3218]], ['world_x' => null, 'world_y' => null, 'world_plane' => null, 'location' => null]],
    'array coordinate' => [['position' => ['x' => [1], 'y' => 3218]], ['world_x' => null, 'world_y' => null, 'world_plane' => null, 'location' => null]],
    'plane too high' => [['position' => ['x' => 3222, 'y' => 3218, 'plane' => 4]], ['world_x' => null, 'world_y' => null, 'world_plane' => null, 'location' => null]],
    'negative plane' => [['position' => ['x' => 3222, 'y' => 3218, 'plane' => -1]], ['world_x' => null, 'world_y' => null, 'world_plane' => null, 'location' => null]],
    'position not an array' => [['position' => 'Lumbridge'], ['world_x' => null, 'world_y' => null, 'world_plane' => null, 'location' => null]],
]);

it('rejects out-of-range positions on a plugin push', function () {
    $rules = RecordFeedEvent::positionRules();

    expect(Validator::make(['position' => ['x' => 3222, 'y' => 3218, 'plane' => 0]], $rules)->passes())->toBeTrue()
        ->and(Validator::make(['position' => ['x' => 3_000_000_000, 'y' => 3218]], $rules)->passes())->toBeFalse()
        ->and(Validator::make(['position' => ['x' => -5, 'y' => 3218]], $rules)->passes())->toBeFalse()
        ->and(Validator::make(['position' => ['x' => 1, 'y' => 1, 'plane' => 4]], $rules)->passes())->toBeFalse();
});

it('backfills positions recorded before the columns existed', function () {
    $positioned = eventAt($this->own, 2268, 3070);
    $sameSpot = eventAt($this->own, 2268, 3070);
    $upstairs = eventAt($this->own, 3208, 3220, 2);
    $without = FeedEvent::factory()->for($this->own)->death()->create();
    $huge = FeedEvent::factory()->for($this->own)->death()->create();
    $broken = FeedEvent::factory()->for($this->own)->death()->create();

    DB::table('feed_events')->update(['world_x' => null, 'world_y' => null, 'world_plane' => null, 'location' => null]);
    // Legacy rows the columns cannot hold, or that are not even JSON objects.
    DB::table('feed_events')->where('id', $huge->id)->update(['payload' => json_encode(['position' => ['x' => 3_000_000_000, 'y' => -4, 'plane' => 99]])]);
    DB::table('feed_events')->where('id', $broken->id)->update(['payload' => '"not an object"']);

    (require database_path('migrations/2026_09_28_171505_backfill_feed_event_position_columns.php'))->up();

    expect(FeedEvent::find($positioned->id)->only(['world_x', 'world_y', 'world_plane', 'location']))
        ->toBe(['world_x' => 2268, 'world_y' => 3070, 'world_plane' => 0, 'location' => 'Somewhere'])
        ->and(FeedEvent::find($sameSpot->id)->world_x)->toBe(2268)
        ->and(FeedEvent::find($upstairs->id)->world_plane)->toBe(2)
        ->and(FeedEvent::find($without->id)->world_x)->toBeNull()
        ->and(DB::table('feed_events')->where('id', $huge->id)->value('world_x'))->toBeNull()
        ->and(DB::table('feed_events')->where('id', $broken->id)->value('world_x'))->toBeNull();
});

it('shows own and opted-in accounts only, dropping everything else', function () {
    $mine = eventAt($this->own, 3222, 3218);
    $shared = eventAt($this->shared, 3165, 3487);
    eventAt($this->hidden, 2272, 4054);
    FeedEvent::factory()->for($this->own)->death()->create();

    $result = app(EventMap::class)->markers($this->viewer, [$this->own->id, $this->shared->id, $this->hidden->id, 999_999, 'abc']);

    expect($result['truncated'])->toBeFalse()
        ->and(collect($result['markers'])->pluck('events.*.id')->flatten()->sort()->values()->all())
        ->toBe([$mine->id, $shared->id]);

    // The hidden account's owner still sees it.
    expect(app(EventMap::class)->markers($this->other, [$this->hidden->id])['markers'])->toHaveCount(1);
});

it('groups events on one tile into a marker, newest first', function () {
    $older = eventAt($this->own, 2268, 3070, 0, ['occurred_at' => now()->subDays(2)]);
    $newer = eventAt($this->shared, 2268, 3070, 0, ['occurred_at' => now()->subDay()]);
    $upstairs = eventAt($this->own, 2268, 3070, 1, ['occurred_at' => now()->subDays(3)]);
    $latest = eventAt($this->own, 3222, 3218, 0, ['occurred_at' => now()]);

    $markers = app(EventMap::class)->markers($this->viewer, [$this->own->id, $this->shared->id])['markers'];

    expect($markers)->toHaveCount(3)
        ->and($markers[0])->toMatchArray(['x' => 3222, 'y' => 3218, 'plane' => 0, 'count' => 1])
        ->and($markers[1])->toMatchArray(['x' => 2268, 'y' => 3070, 'plane' => 0, 'location' => 'Somewhere', 'count' => 2])
        ->and(array_column($markers[1]['events'], 'id'))->toBe([$newer->id, $older->id])
        ->and($markers[2])->toMatchArray(['plane' => 1, 'count' => 1])
        ->and($markers[0]['events'][0])->toBe([
            'id' => $latest->id,
            'account_id' => $this->own->id,
            'username' => 'Mine',
            'type' => FeedEvent::TYPE_QUEST_COMPLETE,
            'title' => 'Quest complete: Dragon Slayer II.',
            'occurred_at' => now()->toIso8601String(),
            'icon' => ['kind' => 'quests_tab', 'slug' => 'blue_quests_icon'],
            'item_id' => null,
        ]);
});

it('lists at most twenty events per marker but counts them all', function () {
    foreach (range(1, 22) as $minutes) {
        eventAt($this->own, 2268, 3070, 0, ['occurred_at' => now()->subMinutes($minutes)]);
    }

    $marker = app(EventMap::class)->markers($this->viewer, [$this->own->id])['markers'][0];

    expect($marker['count'])->toBe(22)
        ->and($marker['events'])->toHaveCount(EventMap::EVENTS_PER_MARKER);
});

it('caps the events at the limit and says so', function () {
    foreach (range(1, 3) as $minutes) {
        eventAt($this->own, 3000 + $minutes, 3000, 0, ['occurred_at' => now()->subMinutes($minutes)]);
    }

    $capped = app(EventMap::class)->markers($this->viewer, [$this->own->id], null, 2);
    $exact = app(EventMap::class)->markers($this->viewer, [$this->own->id], null, 3);

    expect($capped['truncated'])->toBeTrue()
        ->and(array_column($capped['markers'], 'x'))->toBe([3001, 3002])
        ->and($exact['truncated'])->toBeFalse()
        ->and($exact['markers'])->toHaveCount(3);
});

it('filters by event type', function () {
    eventAt($this->own, 3222, 3218);
    $loot = FeedEvent::factory()->for($this->own)->lootDrop()->create([
        'payload' => ['source' => 'Zulrah', 'items' => [], 'total_value' => 100, 'position' => ['x' => 2268, 'y' => 3070]],
    ]);

    $markers = app(EventMap::class)->markers($this->viewer, [$this->own->id], FeedEvent::TYPE_LOOT_DROP)['markers'];

    expect($markers)->toHaveCount(1)
        ->and($markers[0]['events'][0]['id'])->toBe($loot->id)
        ->and($markers[0]['events'][0]['title'])->toBe('Received loot from Zulrah.');
});

it('lists own accounts first, then opted-in accounts that have positioned events', function () {
    eventAt($this->shared, 3222, 3218);
    eventAt($this->hidden, 3222, 3218);
    $spare = Account::factory()->for($this->viewer)->create(['username' => 'Alt']);
    Account::factory()->for($this->other)->create(['username' => 'NoEvents', 'show_events_on_map' => true]);
    eventAt($this->own, 3222, 3218);
    eventAt($this->own, 3223, 3218);

    expect(app(EventMap::class)->selectableAccounts($this->viewer))->toBe([
        ['id' => $spare->id, 'username' => 'Alt', 'account_type' => $spare->account_type->value, 'own' => true, 'event_count' => 0],
        ['id' => $this->own->id, 'username' => 'Mine', 'account_type' => $this->own->account_type->value, 'own' => true, 'event_count' => 2],
        ['id' => $this->shared->id, 'username' => 'Shared', 'account_type' => $this->shared->account_type->value, 'own' => false, 'event_count' => null],
    ]);
});

it('serves the markers as JSON', function () {
    eventAt($this->own, 3222, 3218);
    eventAt($this->hidden, 2272, 4054);

    $this->actingAs($this->viewer)
        ->getJson(route('map.events', ['accounts' => [$this->own->id, $this->hidden->id], 'type' => 'quest_complete']))
        ->assertOk()
        ->assertJsonPath('truncated', false)
        ->assertJsonCount(1, 'markers')
        ->assertJsonPath('markers.0.x', 3222)
        ->assertJsonPath('markers.0.events.0.username', 'Mine');

    $this->actingAs($this->viewer)->getJson(route('map.events'))
        ->assertOk()
        ->assertExactJson(['markers' => [], 'items' => [], 'truncated' => false]);
});

it('validates the map events query', function () {
    $this->actingAs($this->viewer)
        ->getJson(route('map.events', ['accounts' => [$this->own->id], 'type' => 'teleport']))
        ->assertJsonValidationErrors('type');

    $this->actingAs($this->viewer)
        ->getJson(route('map.events', ['accounts' => range(1, 21)]))
        ->assertJsonValidationErrors('accounts');
});

it('requires a login for the map events', function () {
    $this->getJson(route('map.events'))->assertUnauthorized();
});

it('passes the selectable accounts and the preselection to the map page', function () {
    eventAt($this->shared, 3222, 3218);

    $this->actingAs($this->viewer)
        ->get(route('map.index', ['events' => "{$this->own->id},{$this->hidden->id},x,{$this->shared->id}"]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Map/Index')
            ->has('eventAccounts', 2)
            ->where('eventAccounts.0.username', 'Mine')
            ->where('eventAccounts.1.own', false)
            ->where('eventAccountIds', [$this->own->id, $this->shared->id]),
        );

    $this->actingAs($this->viewer)
        ->get(route('map.index', ['events' => ['1']]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->where('eventAccountIds', []));
});

it('exposes where a feed event happened on the Adventurer\'s Log', function () {
    eventAt($this->own, 3208, 3220, 2, ['occurred_at' => now()]);
    FeedEvent::factory()->for($this->own)->questComplete()->create(['occurred_at' => now()->subDay()]);

    $rows = RecentEventResource::collection(app(RecentEvents::class)->page($this->own, viewer: $this->viewer)['events'])->resolve();

    expect($rows[0]['position'])->toBe(['x' => 3208, 'y' => 3220, 'plane' => 2, 'location' => 'Somewhere'])
        ->and($rows[1]['position'])->toBeNull();
});

it('positions demo events idempotently and opts other members in', function () {
    $this->shared->forceFill(['show_events_on_map' => false])->save();
    $owner = User::factory()->withPersonalTeam()->create(['email' => 'zlimon@runemanager.com']);
    $demo = Account::factory()->for($owner)->create();
    FeedEvent::factory()->for($demo)->count(9)->create();
    FeedEvent::factory()->for($this->hidden)->lootDrop()->count(3)->create();

    $this->seed(DemoEventPositionsSeeder::class);

    $positioned = FeedEvent::query()->positioned()->pluck('id')->all();
    expect(count($positioned))->toBeGreaterThanOrEqual(10)
        ->and(collect($positioned)->every(fn (int $id): bool => $id % 8 !== 0))->toBeTrue()
        ->and($this->hidden->fresh()->show_events_on_map)->toBeTrue()
        ->and($demo->fresh()->show_events_on_map)->toBeFalse();

    $before = FeedEvent::query()->positioned()->get(['id', 'world_x', 'world_y'])->toArray();
    $this->seed(DemoEventPositionsSeeder::class);

    expect(FeedEvent::query()->positioned()->get(['id', 'world_x', 'world_y'])->toArray())->toBe($before);
});

it('treats a limit below one as one', function () {
    eventAt($this->own, 3001, 3000, 0, ['occurred_at' => now()]);
    eventAt($this->own, 3002, 3000, 0, ['occurred_at' => now()->subMinute()]);

    $result = app(EventMap::class)->markers($this->viewer, [$this->own->id], null, 0);

    expect($result['markers'])->toHaveCount(1)
        ->and($result['markers'][0]['x'])->toBe(3001)
        ->and($result['truncated'])->toBeTrue();
});

it('does not show an unclaimed account, whatever its flag says', function () {
    $orphan = Account::factory()->create(['user_id' => null]);
    DB::table('accounts')->where('id', $orphan->id)->update(['show_events_on_map' => true]);
    eventAt($orphan, 3222, 3218);

    expect(app(EventMap::class)->markers($this->viewer, [$orphan->id])['markers'])->toBe([])
        ->and(array_column(app(EventMap::class)->selectableAccounts($this->viewer), 'id'))->not->toContain($orphan->id);
});

it('parses the map page preselection', function () {
    $selectable = [['id' => 3], ['id' => 5], ['id' => 8]];

    expect(app(EventMap::class)->preselection($selectable, ' 5,x,3,5,,9, 8'))->toBe([5, 3, 8])
        ->and(app(EventMap::class)->preselection($selectable, null))->toBe([])
        ->and(app(EventMap::class)->preselection($selectable, '1.5,-3'))->toBe([]);
});

it('sends each item picture once, by id, for the listed events only', function () {
    config([
        'database.connections.mongodb-static.dsn' => config('database.connections.mongodb.dsn'),
        'database.connections.mongodb-static.database' => 'gamedata_eventmap_testing',
    ]);
    DB::purge('mongodb-static');
    Item::query()->delete();
    (new Item)->getConnection()->getDatabase()->selectCollection((new Item)->getTable())->insertMany([
        ['id' => '12922', 'name' => 'Tanzanite fang', 'icon' => 'RkFORw==', 'highalch' => 1000],
        ['id' => '11286', 'name' => 'Draconic visage', 'icon' => 'VklTQUdF', 'highalch' => 5000],
    ]);

    try {
        $loot = fn (int $id, int $minutes) => FeedEvent::factory()->for($this->own)->lootDrop()->create([
            'payload' => ['source' => 'Zulrah', 'items' => [['id' => $id, 'quantity' => 1]], 'total_value' => 100, 'position' => ['x' => 2268, 'y' => 3070]],
            'occurred_at' => now()->subMinutes($minutes),
        ]);
        $first = $loot(12922, 1);
        $loot(12922, 2);
        foreach (range(3, 22) as $minutes) {
            $loot(12922, $minutes);
        }
        // The 23rd event at the tile is counted but not listed, so its item is not sent.
        $loot(11286, 30);

        $this->actingAs($this->viewer)
            ->getJson(route('map.events', ['accounts' => [$this->own->id]]))
            ->assertOk()
            ->assertJsonPath('items', ['12922' => 'data:image/jpeg;base64,RkFORw=='])
            ->assertJsonPath('truncated', false)
            ->assertJsonCount(EventMap::EVENTS_PER_MARKER, 'markers.0.events')
            ->assertJsonPath('markers.0.count', 23)
            ->assertJsonPath('markers.0.events.0.id', $first->id)
            ->assertJsonPath('markers.0.events.0.item_id', 12922)
            ->assertJsonPath('markers.0.events.0.icon', null)
            ->assertJsonPath('markers.0.events.0.title', 'Found Tanzanite fang.');
    } finally {
        Item::query()->delete();
    }
});

it('encodes an empty item map as an object', function () {
    eventAt($this->own, 3222, 3218);

    expect($this->actingAs($this->viewer)->get(route('map.events', ['accounts' => [$this->own->id]]))->getContent())
        ->toContain('"items":{}');
});

it('turns the opt-in off when the owner deletes their user', function () {
    app(DeletesUsers::class)->delete($this->other->fresh());

    expect($this->shared->fresh()->user_id)->toBeNull()
        ->and($this->shared->fresh()->show_events_on_map)->toBeFalse();
});

it('turns the opt-in off when a released account is claimed on registration', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CLAN);
    Roles::sync();
    $released = Account::factory()->create(['user_id' => null, 'username' => 'Released']);
    DB::table('accounts')->where('id', $released->id)->update(['show_events_on_map' => true]);

    $this->post('/register', [
        'name' => 'Claimer',
        'email' => 'claimer@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        'account_id' => $released->id,
    ])->assertRedirect();

    expect($released->fresh()->user_id)->not->toBeNull()
        ->and($released->fresh()->show_events_on_map)->toBeFalse();
})->skip(fn () => ! Features::enabled(Features::registration()), 'Registration is not enabled.');

it('turns the opt-in off when a released account is claimed from the plugin', function () {
    $released = Account::factory()->create(['user_id' => null, 'username' => 'Released', 'account_hash' => null]);
    DB::table('accounts')->where('id', $released->id)->update(['show_events_on_map' => true]);
    Sanctum::actingAs($this->viewer);

    $this->putJson('/api/plugin/heartbeat', [], [
        'Accept' => 'application/json',
        'X-Account-Hash' => 'hash-released',
        'X-Account-Username' => 'Released',
    ])->assertSuccessful();

    expect($released->fresh()->user_id)->toBe($this->viewer->id)
        ->and($released->fresh()->show_events_on_map)->toBeFalse();
});

it('keeps the opt-in on an ordinary save', function () {
    $this->shared->forceFill(['level' => 1500])->save();

    expect($this->shared->fresh()->show_events_on_map)->toBeTrue();
});

/**
 * A pinned, positioned milestone on each of the three accounts, so every feed
 * surface (feed, dashboard, pinned gallery, Recent Events) lists it.
 *
 * @return array<string, FeedEvent>
 */
function positionedOnEveryAccount(object $test): array
{
    $events = [];

    foreach (['own', 'shared', 'hidden'] as $index => $name) {
        $events[$name] = eventAt($test->{$name}, 3000 + $index, 3200, 0, ['occurred_at' => now()->subMinutes($index), 'pinned_at' => now()]);
    }

    return $events;
}

/**
 * The payload position of each listed event, keyed by account username.
 *
 * @param  list<array<string, mixed>>  $rows
 * @return array<string, mixed>
 */
function feedPositions(array $rows): array
{
    return collect($rows)->mapWithKeys(fn (array $row): array => [$row['account']['username'] => $row['payload']['position'] ?? null])->all();
}

it('hides a hidden account\'s positions on the feed, for guests and members', function () {
    positionedOnEveryAccount($this);

    $guest = $this->get(route('feed.index'))->assertOk()->inertiaProps('events');
    $member = $this->actingAs($this->viewer)->get(route('feed.index'))->assertOk()->inertiaProps('events');
    $owner = $this->actingAs($this->other)->get(route('feed.index'))->assertOk()->inertiaProps('events');

    expect(feedPositions($guest))->toMatchArray(['Mine' => null, 'Hidden' => null])
        ->and(feedPositions($guest)['Shared'])->toMatchArray(['x' => 3001])
        ->and(feedPositions($member)['Hidden'])->toBeNull()
        ->and(feedPositions($member)['Mine'])->toMatchArray(['x' => 3000])
        ->and(feedPositions($member)['Shared'])->toMatchArray(['x' => 3001])
        ->and(feedPositions($owner)['Hidden'])->toMatchArray(['x' => 3002]);
});

it('hides a hidden account\'s positions on the dashboard', function () {
    positionedOnEveryAccount($this);

    $feed = feedPositions($this->actingAs($this->viewer)->get(route('dashboard'))->assertOk()->inertiaProps('feed'));

    expect($feed['Hidden'])->toBeNull()
        ->and($feed['Shared'])->toMatchArray(['x' => 3001])
        ->and($feed['Mine'])->toMatchArray(['x' => 3000]);
});

it('hides a hidden account\'s positions on its Log, pinned gallery and events JSON', function () {
    positionedOnEveryAccount($this);

    $page = fn (Account $account, User $viewer) => $this->actingAs($viewer)->get(route('accounts.show', $account))->assertOk();
    $json = fn (Account $account, User $viewer) => $this->actingAs($viewer)->getJson(route('accounts.events', $account))->assertOk()->json('data');

    $hidden = $page($this->hidden, $this->viewer);
    expect($hidden->inertiaProps('recentEvents.data.0.position'))->toBeNull()
        ->and($hidden->inertiaProps('pinnedFeed.0.payload'))->not->toHaveKey('position')
        ->and($json($this->hidden, $this->viewer)[0]['position'])->toBeNull();

    $shared = $page($this->shared, $this->viewer);
    expect($shared->inertiaProps('recentEvents.data.0.position'))->toMatchArray(['x' => 3001])
        ->and($shared->inertiaProps('pinnedFeed.0.payload.position'))->toMatchArray(['x' => 3001])
        ->and($json($this->shared, $this->viewer)[0]['position'])->toMatchArray(['x' => 3001]);

    $own = $page($this->hidden, $this->other);
    expect($own->inertiaProps('recentEvents.data.0.position'))->toMatchArray(['x' => 3002])
        ->and($own->inertiaProps('pinnedFeed.0.payload.position'))->toMatchArray(['x' => 3002])
        ->and($json($this->hidden, $this->other)[0]['position'])->toMatchArray(['x' => 3002]);
});

it('never puts a hidden account\'s position on the public broadcast, even inside its owner\'s push', function () {
    $events = positionedOnEveryAccount($this);

    // The broadcast is resolved during the plugin push, as the owner.
    $this->actingAs($this->other);

    expect((new FeedEventCreated($events['hidden']->fresh()))->broadcastWith()['payload'])->not->toHaveKey('position')
        ->and((new FeedEventCreated($events['shared']->fresh()))->broadcastWith()['payload']['position'])->toMatchArray(['x' => 3001]);
});

it('decides position visibility in one place', function () {
    $orphan = Account::factory()->create(['user_id' => null]);

    expect(EventMap::positionVisibleTo($this->viewer, $this->own))->toBeTrue()
        ->and(EventMap::positionVisibleTo(null, $this->own))->toBeFalse()
        ->and(EventMap::positionVisibleTo(null, $this->shared))->toBeTrue()
        ->and(EventMap::positionVisibleTo($this->viewer, $this->hidden))->toBeFalse()
        ->and(EventMap::positionVisibleTo($this->other, $this->hidden))->toBeTrue()
        ->and(EventMap::positionVisibleTo($this->viewer, $orphan->forceFill(['show_events_on_map' => true])))->toBeFalse()
        ->and(EventMap::positionVisibleTo($this->viewer, null))->toBeFalse();
});
