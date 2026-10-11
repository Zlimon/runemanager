<?php

use App\Models\Account;
use App\Models\Announcement;
use App\Models\User;
use App\Support\Roles;
use Inertia\Testing\AssertableInertia;

it('forbids guests from the admin announcements page', function () {
    $this->get(route('admin.announcements'))->assertRedirect(route('login'));
});

it('forbids a user without manage instance from the admin announcements page', function () {
    $this->actingAs(adminUser(Roles::USER))
        ->get(route('admin.announcements'))
        ->assertForbidden();
});

it('lists every announcement for a manage-instance user, expired ones included, with ack counts', function () {
    $author = User::factory()->withPersonalTeam()->create();
    $active = Announcement::factory()->for($author)->create(['title' => 'Active One', 'created_at' => now()->subMinute()]);
    $expired = Announcement::factory()->for($author)->expired()->create(['title' => 'Expired One', 'created_at' => now()]);

    $account = Account::factory()->for($author)->create();
    $active->acknowledgedBy()->attach($account->id);

    $this->actingAs(adminUser())
        ->get(route('admin.announcements'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Announcements/Index')
            ->has('announcements', 2)
            ->where('announcements.0.title', 'Expired One')
            ->where('announcements.0.is_expired', true)
            ->where('announcements.0.acknowledged_count', 0)
            ->where('announcements.1.title', 'Active One')
            ->where('announcements.1.is_expired', false)
            ->where('announcements.1.acknowledged_count', 1),
        );
});

it('lets an admin create an announcement from the admin page via the existing route', function () {
    $this->actingAs(adminUser())
        ->post(route('announcements.store'), [
            'title' => 'Server downtime',
            'body' => 'The site will be briefly unavailable.',
        ])
        ->assertRedirect(route('announcements.index'));

    expect(Announcement::where('title', 'Server downtime')->exists())->toBeTrue();
});

it('lets an admin delete any announcement from the admin page via the existing route', function () {
    $author = User::factory()->withPersonalTeam()->create();
    $announcement = Announcement::factory()->for($author)->create();

    $this->actingAs(adminUser())
        ->delete(route('announcements.destroy', $announcement))
        ->assertRedirect(route('announcements.index'));

    expect(Announcement::find($announcement->id))->toBeNull();
});

it('returns to the admin announcements list when creating from there', function () {
    $this->actingAs(adminUser())
        ->from(route('admin.announcements'))
        ->post(route('announcements.store'), [
            'title' => 'Server downtime',
            'body' => 'The site will be briefly unavailable.',
        ])
        ->assertRedirect(route('admin.announcements'));
});

it('still returns to the public announcements page when creating from there', function () {
    $this->actingAs(adminUser())
        ->from(route('announcements.index'))
        ->post(route('announcements.store'), [
            'title' => 'Server downtime',
            'body' => 'The site will be briefly unavailable.',
        ])
        ->assertRedirect(route('announcements.index'));
});

it('returns to the admin announcements list when deleting from there', function () {
    $author = User::factory()->withPersonalTeam()->create();
    $announcement = Announcement::factory()->for($author)->create();

    $this->actingAs(adminUser())
        ->from(route('admin.announcements'))
        ->delete(route('announcements.destroy', $announcement))
        ->assertRedirect(route('admin.announcements'));

    expect(Announcement::find($announcement->id))->toBeNull();
});

it('still returns to the public announcements page when deleting from there', function () {
    $author = User::factory()->withPersonalTeam()->create();
    $announcement = Announcement::factory()->for($author)->create();

    $this->actingAs(adminUser())
        ->from(route('announcements.index'))
        ->delete(route('announcements.destroy', $announcement))
        ->assertRedirect(route('announcements.index'));
});
