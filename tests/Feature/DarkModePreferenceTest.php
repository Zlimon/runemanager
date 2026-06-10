<?php

use App\Helpers\SettingHelper;
use App\Models\ResourcePack;
use App\Models\User;
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
        ->put(route('user.dark-mode.update'), ['dark_mode' => true])
        ->assertRedirect();

    expect($user->refresh()->dark_mode)->toBeTrue();
});

it('requires a boolean dark_mode value', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->putJson(route('user.dark-mode.update'), ['dark_mode' => 'maybe'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['dark_mode']);
});

it('rejects guests', function () {
    $this->put(route('user.dark-mode.update'), ['dark_mode' => true])
        ->assertRedirect(route('login'));
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
            ->where('can_toggle_dark_mode', false),
        );
});
