<?php

use App\Models\ResourcePack;
use App\Models\User;
use App\Support\Instance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

/**
 * /user/profile composes its sections through FormSection / ActionSection, which
 * now render the shared pack-aware Card. The markup is Vue's business; these
 * guard that the page still renders and still hands those sections everything
 * they read.
 */
it('renders the profile page with the props its sections read', function () {
    $user = User::factory()->withPersonalTeam()->create();

    $this->actingAs($user)
        ->get('/user/profile')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Profile/Show')
            ->has('sessions')
            ->where('confirmsTwoFactorAuthentication', true)
            ->where('auth.user.id', $user->id)
            ->where('jetstream.canUpdateProfileInformation', true)
            ->where('jetstream.canUpdatePassword', true)
            ->where('jetstream.hasAccountDeletionFeatures', true),
        );
});

it('serves the profile page under the active pack theme', function () {
    // The Card surfaces take their colour from the pack variables the Blade root
    // emits, so the page has to arrive with a pack in effect.
    ResourcePack::query()->forceCreate([
        'name' => ResourcePack::VANILLA,
        'alias' => 'Vanilla',
        'version' => '1.0.0',
        'author' => 'test',
        'url' => 'https://example.test/vanilla.zip',
        'tags' => '',
        'dark_mode' => false,
        'background_color' => '#483f33',
        'accent_color' => '#9e8a6f',
    ]);

    $user = User::factory()->withPersonalTeam()->create();

    $this->actingAs($user)
        ->get('/user/profile')
        ->assertOk()
        ->assertSee('data-theme="'.Instance::THEME_VANILLA.'"', false)
        ->assertSee('--pack-bg: #483f33;', false);
});

it('renders the profile page for an untextured viewer too', function () {
    $user = User::factory()->withPersonalTeam()->create(['disable_resource_pack' => true]);

    $this->actingAs($user)
        ->get('/user/profile')
        ->assertOk()
        ->assertSee('no-pack', false);
});
