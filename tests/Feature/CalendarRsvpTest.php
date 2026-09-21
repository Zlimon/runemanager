<?php

use App\Models\Account;
use App\Models\CalendarEvent;
use App\Models\CalendarEventRsvp;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

it('lets a member rsvp with an account they own', function () {
    $member = adminUser(Roles::USER);
    $account = Account::factory()->for($member)->create();
    $event = CalendarEvent::factory()->for(adminUser())->create();

    $this->actingAs($member)
        ->from(route('calendar.index'))
        ->post(route('calendar.rsvp', $event), ['account_id' => $account->id])
        ->assertRedirect(route('calendar.index'));

    expect(CalendarEventRsvp::count())->toBe(1);
    $rsvp = CalendarEventRsvp::first();
    expect($rsvp->calendar_event_id)->toBe($event->id)
        ->and($rsvp->account_id)->toBe($account->id);
});

it('forbids rsvping with an account belonging to someone else', function () {
    $member = adminUser(Roles::USER);
    $stranger = adminUser(Roles::USER);
    $othersAccount = Account::factory()->for($stranger)->create();
    $event = CalendarEvent::factory()->for(adminUser())->create();

    $this->actingAs($member)
        ->post(route('calendar.rsvp', $event), ['account_id' => $othersAccount->id])
        ->assertForbidden();

    expect(CalendarEventRsvp::count())->toBe(0);
});

it('forbids withdrawing someone elses rsvp', function () {
    $member = adminUser(Roles::USER);
    $stranger = adminUser(Roles::USER);
    $othersAccount = Account::factory()->for($stranger)->create();
    $event = CalendarEvent::factory()->for(adminUser())->create();
    $event->rsvps()->create(['account_id' => $othersAccount->id]);

    $this->actingAs($member)
        ->delete(route('calendar.rsvp.destroy', $event), ['account_id' => $othersAccount->id])
        ->assertForbidden();

    expect(CalendarEventRsvp::count())->toBe(1);
});

it('withdraws an rsvp by deleting the row', function () {
    $member = adminUser(Roles::USER);
    $account = Account::factory()->for($member)->create();
    $event = CalendarEvent::factory()->for(adminUser())->create();
    $event->rsvps()->create(['account_id' => $account->id]);

    $this->actingAs($member)
        ->from(route('calendar.index'))
        ->delete(route('calendar.rsvp.destroy', $event), ['account_id' => $account->id])
        ->assertRedirect(route('calendar.index'));

    expect(CalendarEventRsvp::count())->toBe(0);
});

it('is idempotent when the same account rsvps twice', function () {
    $member = adminUser(Roles::USER);
    $account = Account::factory()->for($member)->create();
    $event = CalendarEvent::factory()->for(adminUser())->create();

    foreach (range(1, 2) as $ignored) {
        $this->actingAs($member)
            ->from(route('calendar.index'))
            ->post(route('calendar.rsvp', $event), ['account_id' => $account->id])
            ->assertRedirect(route('calendar.index'));
    }

    expect(CalendarEventRsvp::count())->toBe(1);
});

it('rejects an unauthenticated rsvp and a missing account', function () {
    $event = CalendarEvent::factory()->for(adminUser())->create();

    $this->post(route('calendar.rsvp', $event), ['account_id' => 1])
        ->assertRedirect(route('login'));

    $this->actingAs(adminUser(Roles::USER))
        ->postJson(route('calendar.rsvp', $event), ['account_id' => 999999])
        ->assertStatus(422)
        ->assertJsonValidationErrors('account_id');

    expect(CalendarEventRsvp::count())->toBe(0);
});

it('cascades rsvps away with their event', function () {
    $member = adminUser(Roles::USER);
    $account = Account::factory()->for($member)->create();
    $event = CalendarEvent::factory()->for(adminUser())->create();
    $event->rsvps()->create(['account_id' => $account->id]);

    $event->delete();

    expect(CalendarEventRsvp::count())->toBe(0);
});

it('exposes attendees and rsvp eligibility on the calendar resource', function () {
    $member = adminUser(Roles::USER);
    $account = Account::factory()->for($member)->create([
        'username' => 'Zezima',
        'account_type' => 'ironman',
    ]);
    $event = CalendarEvent::factory()->for(adminUser())->create(['starts_at' => now()->addDay()]);
    $event->rsvps()->create(['account_id' => $account->id]);

    $this->actingAs($member)
        ->get(route('calendar.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('events.0.rsvps', 1)
            ->where('events.0.rsvps.0.account_id', $account->id)
            ->where('events.0.rsvps.0.username', 'Zezima')
            ->where('events.0.rsvps.0.account_type', 'ironman')
            ->where('events.0.can.rsvp', true),
        );

    // A member with no accounts has nothing to attend with.
    $this->actingAs(adminUser(Roles::USER))
        ->get(route('calendar.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('events.0.can.rsvp', false),
        );

    // Guests can read attendance but can't sign up.
    $this->get(route('calendar.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('events.0.rsvps', 1)
            ->where('events.0.can.rsvp', false),
        );
});

it('moves the attendance when a member rsvps with a second account', function () {
    $member = adminUser(Roles::USER);
    $first = Account::factory()->for($member)->create();
    $second = Account::factory()->for($member)->create();
    $event = CalendarEvent::factory()->for(adminUser())->create();

    $this->actingAs($member)
        ->post(route('calendar.rsvp', $event), ['account_id' => $first->id]);
    $this->actingAs($member)
        ->post(route('calendar.rsvp', $event), ['account_id' => $second->id]);

    // A member attends as exactly one account — the second replaces the first
    // rather than adding to it.
    expect(CalendarEventRsvp::count())->toBe(1);
    expect(CalendarEventRsvp::first()->account_id)->toBe($second->id);
});

it('still lets two different members attend the same event', function () {
    $one = adminUser(Roles::USER);
    $two = adminUser(Roles::USER);
    $event = CalendarEvent::factory()->for(adminUser())->create();

    $this->actingAs($one)->post(route('calendar.rsvp', $event), [
        'account_id' => Account::factory()->for($one)->create()->id,
    ]);
    $this->actingAs($two)->post(route('calendar.rsvp', $event), [
        'account_id' => Account::factory()->for($two)->create()->id,
    ]);

    expect(CalendarEventRsvp::count())->toBe(2);
});

it('refuses an rsvp once the event has taken place', function () {
    $member = adminUser(Roles::USER);
    $account = Account::factory()->for($member)->create();
    $event = CalendarEvent::factory()->for(adminUser())->create([
        'starts_at' => now()->subWeek(),
        'ends_at' => now()->subWeek()->addHour(),
    ]);

    $this->actingAs($member)
        ->post(route('calendar.rsvp', $event), ['account_id' => $account->id])
        ->assertForbidden();

    expect(CalendarEventRsvp::count())->toBe(0);
});

it('refuses to withdraw from an event that has taken place', function () {
    $member = adminUser(Roles::USER);
    $account = Account::factory()->for($member)->create();
    $event = CalendarEvent::factory()->for(adminUser())->create([
        'starts_at' => now()->subWeek(),
        'ends_at' => null,
    ]);
    CalendarEventRsvp::create([
        'calendar_event_id' => $event->id,
        'account_id' => $account->id,
        'user_id' => $member->id,
    ]);

    $this->actingAs($member)
        ->delete(route('calendar.rsvp.destroy', $event), ['account_id' => $account->id])
        ->assertForbidden();

    // Attendance on a past event is a record of who turned up, so it stands.
    expect(CalendarEventRsvp::count())->toBe(1);
});

it('treats a multi-day event as open until it ends', function () {
    $member = adminUser(Roles::USER);
    $account = Account::factory()->for($member)->create();
    $event = CalendarEvent::factory()->for(adminUser())->create([
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
    ]);

    $this->actingAs($member)
        ->post(route('calendar.rsvp', $event), ['account_id' => $account->id])
        ->assertRedirect();

    expect(CalendarEventRsvp::count())->toBe(1);
});

it('stops advertising rsvp on a past event in the resource', function () {
    $member = adminUser(Roles::USER);
    Account::factory()->for($member)->create();
    CalendarEvent::factory()->for(adminUser())->create(['starts_at' => now()->subWeek(), 'ends_at' => null]);
    CalendarEvent::factory()->for(adminUser())->create(['starts_at' => now()->addWeek(), 'ends_at' => null]);

    $this->actingAs($member)
        ->get(route('calendar.index', ['from' => now()->subMonth()->toDateString(), 'to' => now()->addMonth()->toDateString()]))
        ->assertInertia(function (AssertableInertia $page) {
            $events = collect($page->toArray()['props']['events']);

            expect($events)->toHaveCount(2);
            expect($events->firstWhere('starts_at', '<', now()->toIso8601String())['can']['rsvp'])->toBeFalse();
            expect($events->firstWhere('starts_at', '>', now()->toIso8601String())['can']['rsvp'])->toBeTrue();
        });
});
