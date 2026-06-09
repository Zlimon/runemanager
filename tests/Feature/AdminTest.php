<?php

use App\Helpers\SettingHelper;
use App\Support\Instance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

it('forbids guests from the admin dashboard', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

it('forbids members from the admin dashboard in casual mode', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CASUAL);

    $this->actingAs(adminUser('member'))
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

it('lets admins view the dashboard', function () {
    $this->actingAs(adminUser())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Dashboard')
            ->has('stats')
            ->where('mode', Instance::MODE_CASUAL),
        );
});

it('treats every authenticated user as admin in group mode', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_GROUP);

    $this->actingAs(adminUser('member'))
        ->get(route('admin.dashboard'))
        ->assertOk();
});

it('lets the owner reach the dashboard via the owner gate', function () {
    $this->actingAs(adminUser('owner'))
        ->get(route('admin.dashboard'))
        ->assertOk();
});

it('lets admins update instance settings', function () {
    $this->actingAs(adminUser())
        ->put(route('admin.settings.update'), [
            'instance_mode' => Instance::MODE_CLAN,
            'clan_name' => 'Knights of Falador',
            'group_name' => '',
            'resource_pack_id' => null,
        ])
        ->assertRedirect();

    expect(Instance::mode())->toBe(Instance::MODE_CLAN);
    expect(Instance::name())->toBe('Knights of Falador');
});

it('validates the instance mode', function () {
    $this->actingAs(adminUser())
        ->putJson(route('admin.settings.update'), [
            'instance_mode' => 'not-a-mode',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('instance_mode');
});

it('shares the admin flag with the frontend for admins', function () {
    $this->actingAs(adminUser())
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('is_admin', true));
});

it('does not flag members as admin in casual mode', function () {
    SettingHelper::setSetting('instance_mode', Instance::MODE_CASUAL);

    $this->actingAs(adminUser('member'))
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('is_admin', false));
});
