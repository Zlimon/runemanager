<?php

use App\Models\CalendarEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

it('renders the calendar index publicly with an upcoming widget', function () {
    $author = User::factory()->withPersonalTeam()->create();
    CalendarEvent::factory()->for($author)->create([
        'title' => 'Coming Soon',
        'starts_at' => now()->addDays(2),
    ]);

    // No actingAs — viewing is public per SPEC §10.2.
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

it('forbids non-creators from deleting other users events', function () {
    $owner = adminUser();
    $intruder = adminUser();
    $event = CalendarEvent::factory()->for($owner)->create();

    $this->actingAs($intruder)
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
