<?php

use App\Helpers\SettingHelper;
use App\Models\ResourcePack;
use App\Models\User;
use App\Support\Instance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

function darkPack(string $name = 'pack-darkish'): ResourcePack
{
    return ResourcePack::query()->forceCreate([
        'name' => $name,
        'alias' => 'Darkish',
        'version' => '1.0.0',
        'author' => 'test',
        'url' => "https://example.test/{$name}.zip",
        'tags' => '',
        'dark_mode' => true,
    ]);
}

it('persists the authenticated user dark-mode preference', function () {
    $user = User::factory()->create(['dark_mode' => false]);

    $this->actingAs($user)
        ->put(route('dark-mode.update'), ['dark_mode' => true])
        ->assertRedirect();

    expect($user->refresh()->dark_mode)->toBeTrue();
});

it('requires a boolean dark_mode value', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->putJson(route('dark-mode.update'), ['dark_mode' => 'maybe'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['dark_mode']);
});

it('lets guests persist their choice to a cookie', function () {
    $this->put(route('dark-mode.update'), ['dark_mode' => true])
        ->assertRedirect()
        ->assertCookie(Instance::DARK_MODE_COOKIE, '1', encrypted: false);
});

it('keeps the toggle available and user-driven even with a pack active', function () {
    $pack = darkPack();
    // Pack flag is dark, but the user prefers light — the user's choice wins.
    $user = User::factory()->withPersonalTeam()->create([
        'dark_mode' => false,
        'resource_pack_id' => $pack->id,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('can_toggle_dark_mode', true)
            ->where('dark_mode', false),
        );
});

it('falls back to the pack dark flag for logged-out visitors', function () {
    $pack = darkPack();
    SettingHelper::setSetting('resource_pack_id', $pack->id, 'int');

    $this->get(route('announcements.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('dark_mode', true)
            ->where('can_toggle_dark_mode', true),
        );
});

it('lets the instance default override an unreliable pack flag for guests', function () {
    // Pack says dark, but the owner forces light at the instance level.
    $pack = darkPack();
    SettingHelper::setSetting('resource_pack_id', $pack->id, 'int');
    SettingHelper::setSetting('default_dark_mode', 'light');

    $this->get(route('announcements.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('dark_mode', false));
});

it('forces dark for guests when the instance default is dark and the pack is light', function () {
    SettingHelper::setSetting('default_dark_mode', 'dark');

    $this->get(route('announcements.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('dark_mode', true));
});

it('lets a guest cookie choice win over the instance default', function () {
    SettingHelper::setSetting('default_dark_mode', 'dark');

    $this->withUnencryptedCookie(Instance::DARK_MODE_COOKIE, '0')
        ->get(route('announcements.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('dark_mode', false));
});

it('seeds a logged-in user with no preference from the instance default', function () {
    SettingHelper::setSetting('default_dark_mode', 'dark');
    $user = User::factory()->withPersonalTeam()->create(['dark_mode' => null]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('dark_mode', true));
});
