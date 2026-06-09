<?php

use App\Helpers\SettingHelper;
use App\Models\User;
use App\Support\Instance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function setupOwner(): User
{
    foreach (['owner', 'admin', 'member'] as $role) {
        Role::findOrCreate($role, 'web');
    }

    return tap(User::factory()->withPersonalTeam()->create())->assignRole('owner');
}

beforeEach(fn () => SettingHelper::setSetting('instance_configured', false, 'bool'));

it('redirects an authenticated user to setup until the instance is configured', function () {
    $this->actingAs(setupOwner())
        ->get(route('dashboard'))
        ->assertRedirect(route('admin.settings'));
});

it('also gates the public pages for an authenticated user during setup', function () {
    $owner = setupOwner();

    foreach (['feed.index', 'calendar.index', 'announcements.index'] as $route) {
        $this->actingAs($owner)
            ->get(route($route))
            ->assertRedirect(route('admin.settings'));
    }
});

it('still serves the public pages to guests during setup', function () {
    $this->get(route('announcements.index'))->assertOk();
    $this->get(route('calendar.index'))->assertOk();
});

it('lets the owner reach the setup page while unconfigured', function () {
    $this->actingAs(setupOwner())
        ->get(route('admin.settings'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Settings')
            ->where('configured', false),
        );
});

it('unblocks the site once setup is completed', function () {
    $owner = setupOwner();

    $this->actingAs($owner)
        ->put(route('admin.settings.update'), ['instance_mode' => Instance::MODE_CASUAL])
        ->assertRedirect();

    expect(Instance::isConfigured())->toBeTrue();

    $this->actingAs($owner)->get(route('dashboard'))->assertOk();
});
