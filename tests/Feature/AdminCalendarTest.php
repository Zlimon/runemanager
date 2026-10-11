<?php

use App\Models\CalendarEvent;
use App\Models\User;
use App\Support\Instance;
use App\Support\Roles;
use Inertia\Testing\AssertableInertia;

it('forbids guests from the admin calendar page', function () {
    $this->get(route('admin.calendar'))->assertRedirect(route('login'));
});

it('forbids a user without manage instance from the admin calendar page', function () {
    $this->actingAs(adminUser(Roles::USER))
        ->get(route('admin.calendar'))
        ->assertForbidden();
});

it('renders the admin calendar page grouped into upcoming and past for a manage-instance user', function () {
    $author = User::factory()->withPersonalTeam()->create();
    CalendarEvent::factory()->for($author)->create(['title' => 'Upcoming Raid']);
    CalendarEvent::factory()->for($author)->past()->create(['title' => 'Old Raid']);

    $this->actingAs(adminUser())
        ->get(route('admin.calendar'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Calendar/Index')
            ->has('upcoming', 1)
            ->has('past', 1)
            ->where('upcoming.0.title', 'Upcoming Raid')
            ->where('past.0.title', 'Old Raid')
            ->has('eventTypes')
            ->has('highlightColors'),
        );
});

it('persists and round-trips the highlight colour setting', function () {
    $this->actingAs(adminUser())
        ->put(route('admin.calendar.settings'), [
            'calendar_highlight_colors' => ['red', 'orange'],
        ])
        ->assertRedirect();

    expect(Instance::calendarHighlightColors())->toBe(['red', 'orange']);

    $this->actingAs(adminUser())
        ->get(route('admin.calendar'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('highlightColors', ['red', 'orange']),
        );
});

it('rejects an unknown highlight colour', function () {
    $this->actingAs(adminUser())
        ->putJson(route('admin.calendar.settings'), [
            'calendar_highlight_colors' => ['chartreuse'],
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('calendar_highlight_colors.0');
});

it('requires the highlight colour list to be present', function () {
    $this->actingAs(adminUser())
        ->putJson(route('admin.calendar.settings'), [])
        ->assertStatus(422)
        ->assertJsonValidationErrors('calendar_highlight_colors');
});

it('accepts an empty highlight colour list', function () {
    $this->actingAs(adminUser())
        ->put(route('admin.calendar.settings'), ['calendar_highlight_colors' => []])
        ->assertRedirect();

    expect(Instance::calendarHighlightColors())->toBe([]);
});

it('forbids a user without manage instance from updating the highlight setting', function () {
    $this->actingAs(adminUser(Roles::USER))
        ->putJson(route('admin.calendar.settings'), [
            'calendar_highlight_colors' => ['red'],
        ])
        ->assertForbidden();
});

it('lets an admin update an event created by a different user', function () {
    $author = User::factory()->withPersonalTeam()->create();
    $event = CalendarEvent::factory()->for($author)->create(['title' => 'Original title']);

    $this->actingAs(adminUser())
        ->put(route('calendar.update', $event), [
            'title' => 'Renamed by admin',
            'event_type' => $event->event_type->value,
            'starts_at' => $event->starts_at->toIso8601String(),
        ])
        ->assertRedirect();

    expect($event->fresh()->title)->toBe('Renamed by admin');
});

it('lets an admin delete an event created by a different user', function () {
    $author = User::factory()->withPersonalTeam()->create();
    $event = CalendarEvent::factory()->for($author)->create();

    $this->actingAs(adminUser())
        ->delete(route('calendar.destroy', $event))
        ->assertRedirect();

    expect(CalendarEvent::find($event->id))->toBeNull();
});
