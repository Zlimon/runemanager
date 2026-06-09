<?php

use App\Models\Announcement;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

it('renders the announcements index publicly with only active announcements newest-first', function () {
    $author = User::factory()->withPersonalTeam()->create();
    Announcement::factory()->for($author)->create(['title' => 'Older', 'created_at' => now()->subDay()]);
    Announcement::factory()->for($author)->create(['title' => 'Newer']);
    Announcement::factory()->expired()->for($author)->create(['title' => 'Expired']);

    // No actingAs — viewing is public per SPEC §9.3.
    $this->get(route('announcements.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Announcements/Index')
            ->has('announcements', 2)
            ->where('announcements.0.title', 'Newer')
            ->where('announcements.1.title', 'Older'),
        );
});

it('lets admins publish announcements', function () {
    $user = adminUser();

    $this->actingAs($user)
        ->post(route('announcements.store'), [
            'title' => 'Server maintenance',
            'body' => 'Down for an hour tonight.',
            'expires_at' => now()->addWeek()->toIso8601String(),
        ])
        ->assertRedirect(route('announcements.index'));

    $announcement = Announcement::firstOrFail();
    expect($announcement->title)->toBe('Server maintenance');
    expect($announcement->user_id)->toBe($user->id);
});

it('validates the announcement payload', function () {
    $user = adminUser();

    $this->actingAs($user)
        ->postJson(route('announcements.store'), ['title' => '', 'body' => '', 'expires_at' => now()->subDay()->toIso8601String()])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'body', 'expires_at']);
});

it('rejects guests from publishing', function () {
    $this->post(route('announcements.store'), ['title' => 'x', 'body' => 'y'])
        ->assertRedirect(route('login'));
});

it('rejects non-admins from publishing', function () {
    $user = adminUser(Roles::USER);

    $this->actingAs($user)
        ->post(route('announcements.store'), ['title' => 'x', 'body' => 'y'])
        ->assertForbidden();
});

it('lets the author delete but forbids other admins', function () {
    $author = adminUser();
    $other = adminUser();
    $announcement = Announcement::factory()->for($author)->create();

    $this->actingAs($other)
        ->delete(route('announcements.destroy', $announcement))
        ->assertForbidden();

    $this->actingAs($author)
        ->delete(route('announcements.destroy', $announcement))
        ->assertRedirect(route('announcements.index'));

    expect(Announcement::count())->toBe(0);
});
