<?php

use App\Helpers\SettingHelper;
use App\Models\ResourcePack;
use App\Models\User;
use App\Support\Instance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

/**
 * Appearance rules (SPEC §6.2):
 *   - no resource pack  → untextured, light + dark, toggle shown
 *   - Default Vanilla   → textured, one fixed scheme, toggle hidden
 *   - any other pack    → unchanged for now; rides the light/dark pair
 */
function vanillaPack(): ResourcePack
{
    return ResourcePack::query()->forceCreate([
        'name' => ResourcePack::VANILLA,
        'alias' => 'Default Vanilla',
        'version' => '1.0.0',
        'author' => 'test',
        'url' => 'https://example.test/vanilla.zip',
        'tags' => '',
        'dark_mode' => false,
    ]);
}

it('gives the untextured interface both modes and a toggle', function () {
    $user = User::factory()->withPersonalTeam()->create([
        'disable_resource_pack' => true,
        'dark_mode' => true,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('can_toggle_dark_mode', true)
            ->where('dark_mode', true)
            ->where('theme', Instance::THEME_DARK)
            ->where('no_pack', true)
            ->where('pack', null),
        );
});

it('serves the light base theme to an untextured viewer in light mode', function () {
    $user = User::factory()->withPersonalTeam()->create([
        'disable_resource_pack' => true,
        'dark_mode' => false,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('theme', Instance::THEME_LIGHT)
            ->where('can_toggle_dark_mode', true),
        );
});

it('hides the toggle and pins one fixed theme for Default Vanilla', function () {
    $pack = vanillaPack();
    // Even a stored dark preference must not reach Vanilla's fixed scheme.
    $user = User::factory()->withPersonalTeam()->create([
        'dark_mode' => true,
        'resource_pack_id' => $pack->id,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('can_toggle_dark_mode', false)
            ->where('dark_mode', false)
            ->where('theme', Instance::THEME_VANILLA),
        );
});

it('ignores the instance default appearance while Default Vanilla is active', function () {
    vanillaPack();
    SettingHelper::setSetting('default_dark_mode', 'dark');

    $this->get(route('announcements.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('dark_mode', false)
            ->where('theme', Instance::THEME_VANILLA)
            ->where('can_toggle_dark_mode', false),
        );
});

it('keeps other packs on the light/dark pair for now', function () {
    $pack = darkPack();
    $user = User::factory()->withPersonalTeam()->create([
        'dark_mode' => null,
        'resource_pack_id' => $pack->id,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('can_toggle_dark_mode', true)
            ->where('theme', Instance::THEME_DARK),
        );
});

it('lets a guest opt out of resource packs with a cookie', function () {
    vanillaPack();

    $this->putJson(route('user.resource-pack.update'), ['resource_pack_id' => 'none'])
        ->assertOk()
        ->assertCookie(Instance::RESOURCE_PACK_COOKIE, ResourcePack::GUEST_NO_PACK, encrypted: false);
});

it('serves a guest the untextured, toggleable interface once they opt out', function () {
    vanillaPack();

    $this->withUnencryptedCookie(Instance::RESOURCE_PACK_COOKIE, ResourcePack::GUEST_NO_PACK)
        ->get(route('announcements.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('no_pack', true)
            ->where('can_toggle_dark_mode', true)
            ->where('theme', Instance::THEME_LIGHT),
        );
});

it('honours a guest light/dark choice only once they are untextured', function () {
    vanillaPack();

    $this->withUnencryptedCookie(Instance::RESOURCE_PACK_COOKIE, ResourcePack::GUEST_NO_PACK)
        ->withUnencryptedCookie(Instance::DARK_MODE_COOKIE, '1')
        ->get(route('announcements.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('dark_mode', true)
            ->where('theme', Instance::THEME_DARK),
        );
});

it('lets a guest pick a specific installed pack', function () {
    vanillaPack();
    $pack = darkPack();

    $this->putJson(route('user.resource-pack.update'), ['resource_pack_id' => $pack->id])
        ->assertOk()
        ->assertCookie(Instance::RESOURCE_PACK_COOKIE, (string) $pack->id, encrypted: false);

    $this->withUnencryptedCookie(Instance::RESOURCE_PACK_COOKIE, (string) $pack->id)
        ->get(route('announcements.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('pack.name', $pack->name));
});

it('falls back to the instance default when a guest cookie names a deleted pack', function () {
    vanillaPack();

    $this->withUnencryptedCookie(Instance::RESOURCE_PACK_COOKIE, '999999')
        ->get(route('announcements.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('pack.name', ResourcePack::VANILLA)
            ->where('no_pack', false),
        );
});

it('rejects a guest pack id that does not exist', function () {
    $this->putJson(route('user.resource-pack.update'), ['resource_pack_id' => 999999])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['resource_pack_id']);
});

it('stamps the theme and pack state on the server-rendered html element', function () {
    vanillaPack();

    $this->get(route('announcements.index'))
        ->assertSee('data-theme="'.Instance::THEME_VANILLA.'"', false)
        ->assertSee('has-pack', false);
});

it('marks the document as untextured when no pack is active', function () {
    $user = User::factory()->withPersonalTeam()->create(['disable_resource_pack' => true]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSee('no-pack', false)
        ->assertSee('data-theme="'.Instance::THEME_LIGHT.'"', false);
});
