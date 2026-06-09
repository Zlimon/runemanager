<?php

use App\Models\CalendarEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

it('renders the calendar index publicly with split upcoming + past lists', function () {
    $author = User::factory()->withPersonalTeam()->create();
    CalendarEvent::factory()->for($author)->create(['title' => 'Coming Soon']);
    CalendarEvent::factory()->past()->for($author)->create(['title' => 'Already Done']);

    // No actingAs — viewing is public per SPEC §10.2.
    $this->get(route('calendar.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Calendar/Index')
            ->has('upcoming', 1)
            ->where('upcoming.0.title', 'Coming Soon')
            ->has('past', 1)
            ->where('past.0.title', 'Already Done')
            ->has('eventTypes', 4),
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
