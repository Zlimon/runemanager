<?php

use App\Helpers\SettingHelper;
use App\Http\Resources\CalendarEventResource;
use App\Models\CalendarEvent;
use App\Models\Item;
use App\Models\User;
use App\Support\Instance;
use App\Support\Roles;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use MongoDB\Driver\Monitoring\CommandFailedEvent;
use MongoDB\Driver\Monitoring\CommandStartedEvent;
use MongoDB\Driver\Monitoring\CommandSubscriber;
use MongoDB\Driver\Monitoring\CommandSucceededEvent;

/*
 * mongodb-static holds the real item catalogue and is shared + read-only in
 * tests (see phpunit.xml). The item-icon cases below have to seed it, so they
 * point the connection at a scratch database first — the same trick
 * ItemsSyncTest uses. Called per test rather than in a global beforeEach so the
 * rest of this file keeps running without Mongo.
 */
function useScratchItemCatalogue(): void
{
    config([
        'database.connections.mongodb-static.dsn' => config('database.connections.mongodb.dsn'),
        'database.connections.mongodb-static.database' => 'gamedata_calendar_testing',
    ]);
    DB::purge('mongodb-static');
    Item::query()->delete();
}

/*
 * Seed through the raw collection — Eloquent inserts alias `id` to `_id`,
 * losing the string OSRS id field the lookups match on.
 */
function seedCalendarItem(array $document): void
{
    (new Item)->getConnection()
        ->getDatabase()
        ->selectCollection((new Item)->getTable())
        ->insertOne($document);
}

/** Count Mongo `aggregate` commands issued while running $callback. */
function countItemAggregations(Closure $callback): int
{
    $subscriber = new class implements CommandSubscriber
    {
        public int $aggregates = 0;

        public function commandStarted(CommandStartedEvent $event): void
        {
            if ($event->getCommandName() === 'aggregate') {
                $this->aggregates++;
            }
        }

        public function commandSucceeded(CommandSucceededEvent $event): void {}

        public function commandFailed(CommandFailedEvent $event): void {}
    };

    MongoDB\Driver\Monitoring\addSubscriber($subscriber);

    try {
        $callback();
    } finally {
        MongoDB\Driver\Monitoring\removeSubscriber($subscriber);
    }

    return $subscriber->aggregates;
}

it('renders the calendar index publicly with an upcoming widget', function () {
    $author = User::factory()->withPersonalTeam()->create();
    CalendarEvent::factory()->for($author)->create([
        'title' => 'Coming Soon',
        'starts_at' => now()->addDays(2),
    ]);

    // No actingAs — viewing is public.
    $this->get(route('calendar.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Calendar/Index')
            ->has('events')
            ->has('upcoming', 1)
            ->where('upcoming.0.title', 'Coming Soon')
            ->has('eventTypes', 4),
        );
});

it('returns events overlapping a requested from/to range', function () {
    $author = User::factory()->withPersonalTeam()->create();
    CalendarEvent::factory()->for($author)->create([
        'title' => 'Jan Raid',
        'starts_at' => '2026-01-15 20:00:00',
    ]);
    // A multi-day event starting before the window but ending inside it.
    CalendarEvent::factory()->for($author)->create([
        'title' => 'New Year Bash',
        'starts_at' => '2025-12-30 20:00:00',
        'ends_at' => '2026-01-02 02:00:00',
    ]);

    $this->get(route('calendar.index', ['from' => '2026-01-01', 'to' => '2026-01-31']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('events', 2)
            ->where('events.0.title', 'New Year Bash')
            ->where('events.1.title', 'Jan Raid'),
        );
});

it('lets admins create events', function () {
    $user = adminUser();

    $this->actingAs($user)
        ->post(route('calendar.store'), [
            'title' => 'CoX mass',
            'description' => 'Bring overloads.',
            'event_type' => 'pvm_mass',
            'starts_at' => now()->addDay()->toIso8601String(),
            'ends_at' => now()->addDay()->addHours(2)->toIso8601String(),
        ])
        ->assertRedirect(route('calendar.index'));

    expect(CalendarEvent::count())->toBe(1);
    $event = CalendarEvent::first();
    expect($event->title)->toBe('CoX mass');
    expect($event->user_id)->toBe($user->id);
    expect($event->event_type->value)->toBe('pvm_mass');
});

it('rejects unauthenticated creates', function () {
    $this->post(route('calendar.store'), [
        'title' => 'Anonymous Event',
        'event_type' => 'custom',
        'starts_at' => now()->addDay()->toIso8601String(),
    ])->assertRedirect(route('login'));

    expect(CalendarEvent::count())->toBe(0);
});

it('rejects an end-before-start window', function () {
    $user = adminUser();

    $this->actingAs($user)
        ->post(route('calendar.store'), [
            'title' => 'Bad window',
            'event_type' => 'custom',
            'starts_at' => now()->addDay()->toIso8601String(),
            'ends_at' => now()->subDay()->toIso8601String(),
        ])
        ->assertSessionHasErrors('ends_at');

    expect(CalendarEvent::count())->toBe(0);
});

it('rejects an unknown event_type', function () {
    $user = adminUser();

    $this->actingAs($user)
        ->post(route('calendar.store'), [
            'title' => 'Bad type',
            'event_type' => 'not-real',
            'starts_at' => now()->addDay()->toIso8601String(),
        ])
        ->assertSessionHasErrors('event_type');
});

it('lets the creator delete their own event', function () {
    $user = adminUser();
    $event = CalendarEvent::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('calendar.destroy', $event))
        ->assertRedirect(route('calendar.index'));

    expect(CalendarEvent::count())->toBe(0);
});

it('lets an admin delete another members event', function () {
    $author = adminUser();
    $admin = adminUser();
    $event = CalendarEvent::factory()->for($author)->create();

    $this->actingAs($admin)
        ->delete(route('calendar.destroy', $event))
        ->assertRedirect(route('calendar.index'));

    expect(CalendarEvent::count())->toBe(0);
});

it('forbids a member without manage calendar from deleting someone elses event', function () {
    $author = adminUser();
    $member = adminUser(Roles::USER);
    $event = CalendarEvent::factory()->for($author)->create();

    $this->actingAs($member)
        ->delete(route('calendar.destroy', $event))
        ->assertForbidden();

    expect(CalendarEvent::count())->toBe(1);
});

it('rejects events scheduled before today', function () {
    $this->actingAs(adminUser())
        ->postJson(route('calendar.store'), [
            'title' => 'Yesterday Raid',
            'event_type' => 'pvm_mass',
            'starts_at' => now()->subDay()->toIso8601String(),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('starts_at');

    expect(CalendarEvent::count())->toBe(0);
});

it('stores the chosen colour and rejects an unknown one', function () {
    $user = adminUser();

    $this->actingAs($user)
        ->post(route('calendar.store'), [
            'title' => 'Blue Event',
            'event_type' => 'pvm_mass',
            'color' => 'blue',
            'starts_at' => now()->addDay()->toIso8601String(),
        ])
        ->assertRedirect();
    expect(CalendarEvent::first()->color)->toBe('blue');

    $this->actingAs($user)
        ->postJson(route('calendar.store'), [
            'title' => 'Bad Colour',
            'event_type' => 'pvm_mass',
            'color' => 'chartreuse',
            'starts_at' => now()->addDay()->toIso8601String(),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('color');
});

it('lets an admin update another members event', function () {
    $author = adminUser();
    $admin = adminUser();
    $event = CalendarEvent::factory()->for($author)->create(['title' => 'Old title']);

    $this->actingAs($admin)
        ->put(route('calendar.update', $event), [
            'title' => 'Rescheduled raid',
            'event_type' => 'pvm_mass',
            'color' => 'teal',
            'starts_at' => now()->addDays(3)->toIso8601String(),
        ])
        ->assertRedirect(route('calendar.index'));

    expect($event->fresh()->title)->toBe('Rescheduled raid')
        ->and($event->fresh()->color)->toBe('teal');
});

it('forbids a member without manage calendar from updating an event', function () {
    $author = adminUser();
    $member = adminUser(Roles::USER);
    $event = CalendarEvent::factory()->for($author)->create(['title' => 'Untouched']);

    $this->actingAs($member)
        ->put(route('calendar.update', $event), [
            'title' => 'Hijacked',
            'event_type' => 'custom',
            'starts_at' => now()->addDay()->toIso8601String(),
        ])
        ->assertForbidden();

    expect($event->fresh()->title)->toBe('Untouched');
});

it('allows editing an event that has already started', function () {
    $admin = adminUser();
    $event = CalendarEvent::factory()->for($admin)->past()->create();

    $this->actingAs($admin)
        ->put(route('calendar.update', $event), [
            'title' => 'Corrected after the fact',
            'event_type' => 'custom',
            'starts_at' => now()->subDays(2)->toIso8601String(),
        ])
        ->assertRedirect(route('calendar.index'));

    expect($event->fresh()->title)->toBe('Corrected after the fact');
});

it('resolves update/delete permissions server-side per viewer', function () {
    $author = adminUser();
    $admin = adminUser();
    $member = adminUser(Roles::USER);
    CalendarEvent::factory()->for($author)->create(['starts_at' => now()->addDay()]);

    $assertCan = function (?User $user, bool $expected) {
        $request = $user ? $this->actingAs($user) : $this;

        $request->get(route('calendar.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('events.0.can.update', $expected)
                ->where('events.0.can.delete', $expected),
            );
    };

    $assertCan($author, true);   // creator
    $assertCan($admin, true);    // holds `manage calendar`
    $assertCan($member, false);  // plain member
    $assertCan(null, false);     // guest
});

it('marks events highlighted when their colour is in the instance list', function () {
    SettingHelper::setSetting('calendar_highlight_colors', json_encode(['red', 'orange']));

    $author = adminUser();
    CalendarEvent::factory()->for($author)->create(['color' => 'red', 'starts_at' => now()->addDay()]);
    CalendarEvent::factory()->for($author)->create(['color' => 'teal', 'starts_at' => now()->addDays(2)]);
    CalendarEvent::factory()->for($author)->create(['color' => null, 'starts_at' => now()->addDays(3)]);

    $this->get(route('calendar.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('events.0.highlighted', true)
            ->where('events.1.highlighted', false)
            ->where('events.2.highlighted', false)
            ->where('highlightColors', ['red', 'orange']),
        );
});

it('falls back to a default highlight palette but honours an explicit empty list', function () {
    expect(Instance::calendarHighlightColors())->toBe(['red']);

    SettingHelper::setSetting('calendar_highlight_colors', json_encode([]));
    expect(Instance::calendarHighlightColors())->toBe([]);

    SettingHelper::setSetting('calendar_highlight_colors', 'not json');
    expect(Instance::calendarHighlightColors())->toBe(['red']);
});

it('hydrates the events item icon from the static catalogue', function () {
    useScratchItemCatalogue();
    seedCalendarItem(['id' => '4151', 'name' => 'Abyssal whip', 'icon' => 'QUJD']);

    $author = adminUser();
    CalendarEvent::factory()->for($author)->create([
        'item_id' => 4151,
        'starts_at' => now()->addDay(),
    ]);
    CalendarEvent::factory()->for($author)->create([
        'item_id' => null,
        'starts_at' => now()->addDays(2),
    ]);

    $this->get(route('calendar.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('events.0.item.id', 4151)
            ->where('events.0.item.name', 'Abyssal whip')
            ->where('events.0.item.icon', 'QUJD')
            ->where('events.1.item', null),
        );
});

it('resolves every events item in a single mongo query', function () {
    useScratchItemCatalogue();
    foreach (range(1, 12) as $id) {
        seedCalendarItem(['id' => (string) $id, 'name' => 'Item '.$id, 'icon' => 'QUJD']);
    }

    $author = adminUser();
    foreach (range(1, 12) as $id) {
        CalendarEvent::factory()->for($author)->create([
            'item_id' => $id,
            'starts_at' => now()->addDays($id),
        ]);
    }

    $events = CalendarEvent::query()->orderBy('starts_at')->get();

    $resolved = null;
    $aggregations = countItemAggregations(function () use ($events, &$resolved) {
        $resolved = CalendarEventResource::collection($events)->resolve();
    });

    // One aggregation for the whole 42-day grid, not one per event.
    expect($aggregations)->toBe(1)
        ->and($resolved)->toHaveCount(12)
        ->and($resolved[0]['item']['name'])->toBe('Item 1')
        ->and($resolved[11]['item']['name'])->toBe('Item 12');
});

it('does not query mongo at all when no event carries an item', function () {
    useScratchItemCatalogue();

    $author = adminUser();
    CalendarEvent::factory()->count(3)->for($author)->create(['item_id' => null]);

    $aggregations = countItemAggregations(function () {
        CalendarEventResource::collection(CalendarEvent::query()->get())->resolve();
    });

    expect($aggregations)->toBe(0);
});

it('searches the item catalogue for the picker', function () {
    useScratchItemCatalogue();
    seedCalendarItem(['id' => '4151', 'name' => 'Abyssal whip', 'icon' => 'QUJD']);
    seedCalendarItem(['id' => '4152', 'name' => 'Abyssal dagger', 'icon' => 'REVG']);
    seedCalendarItem(['id' => '4153', 'name' => 'Dragon scimitar', 'icon' => 'R0hJ']);
    // Noted/placeholder duplicates must not clutter the picker.
    seedCalendarItem(['id' => '4154', 'name' => 'Abyssal whip', 'icon' => 'QUJD', 'noted' => true]);

    $this->actingAs(adminUser())
        ->getJson(route('items.search', ['q' => 'abyssal']))
        ->assertOk()
        ->assertJsonCount(2)
        ->assertJson([
            ['id' => 4152, 'name' => 'Abyssal dagger', 'icon' => 'REVG'],
            ['id' => 4151, 'name' => 'Abyssal whip', 'icon' => 'QUJD'],
        ]);
});

it('caps item search results and returns nothing for a blank term', function () {
    useScratchItemCatalogue();
    foreach (range(1, 25) as $id) {
        seedCalendarItem(['id' => (string) $id, 'name' => 'Rune bar '.$id]);
    }

    $this->actingAs(adminUser())
        ->getJson(route('items.search', ['q' => 'rune bar']))
        ->assertOk()
        ->assertJsonCount(20);

    $this->actingAs(adminUser())
        ->getJson(route('items.search', ['q' => '']))
        ->assertOk()
        ->assertJsonCount(0);
});

it('rejects an item search term longer than 64 characters', function () {
    $this->actingAs(adminUser())
        ->getJson(route('items.search', ['q' => str_repeat('a', 65)]))
        ->assertStatus(422)
        ->assertJsonValidationErrors('q');
});

it('lets guests search items, since the register page picks an icon', function () {
    $this->getJson(route('items.search', ['q' => 'whip']))
        ->assertOk();
});
