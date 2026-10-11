<?php

use App\Helpers\SettingHelper;
use App\Models\ResourcePack;
use App\Models\User;
use App\Services\ResourcePacks\InstallResourcePack;
use App\Support\Instance;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Queue::fake();
});

/**
 * Selecting a resource pack is choosing an appearance, so it clears the viewer's
 * stale light/dark toggle and lets the new pack's flag govern again. The
 * precedence itself (user > guest cookie > instance default > pack flag) is
 * untouched — see AppearanceRulesTest / DarkModePreferenceTest for that.
 */
function syncPack(string $name, bool $dark): ResourcePack
{
    return ResourcePack::query()->forceCreate([
        'name' => $name,
        'alias' => ucfirst($name),
        'version' => '1.0.0',
        'author' => 'test',
        'url' => "https://example.test/{$name}.zip",
        'tags' => $dark ? 'dark' : '',
        'dark_mode' => $dark,
    ]);
}

function syncUser(array $attributes = []): User
{
    return User::query()->forceCreate(array_merge([
        'name' => 'Sync Tester',
        'email' => 'sync@test.local',
        'email_verified_at' => now(),
        'password' => Hash::make('pw'),
        'icon_id' => 0,
    ], $attributes));
}

it('drops a stale light preference when the member picks a dark pack', function () {
    $light = syncPack('pack-light', false);
    $dark = syncPack('pack-dark', true);
    $user = syncUser(['resource_pack_id' => $light->id, 'dark_mode' => false]);

    $this->actingAs($user)
        ->putJson(route('user.resource-pack.update'), ['resource_pack_id' => $dark->id])
        ->assertSuccessful();

    expect($user->fresh()->dark_mode)->toBeNull();

    $this->actingAs($user->fresh())
        ->get(route('announcements.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('dark_mode', true)
            ->where('theme', Instance::THEME_DARK),
        );
});

it('drops a stale dark preference when the member picks a light pack', function () {
    $dark = syncPack('pack-dark', true);
    $light = syncPack('pack-light', false);
    $user = syncUser(['resource_pack_id' => $dark->id, 'dark_mode' => true]);

    $this->actingAs($user)
        ->putJson(route('user.resource-pack.update'), ['resource_pack_id' => $light->id])
        ->assertSuccessful();

    expect($user->fresh()->dark_mode)->toBeNull();

    $this->actingAs($user->fresh())
        ->get(route('announcements.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('dark_mode', false)
            ->where('theme', Instance::THEME_LIGHT),
        );
});

it('keeps a deliberate toggle when the member re-saves the pack already in effect', function () {
    $dark = syncPack('pack-dark', true);
    $user = syncUser(['resource_pack_id' => $dark->id, 'dark_mode' => false]);

    $this->actingAs($user)
        ->putJson(route('user.resource-pack.update'), ['resource_pack_id' => $dark->id])
        ->assertSuccessful();

    expect($user->fresh()->dark_mode)->toBeFalse();

    $this->actingAs($user->fresh())
        ->get(route('announcements.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('dark_mode', false));
});

it('lets the member toggle back after a pack change, and the toggle then sticks', function () {
    $light = syncPack('pack-light', false);
    $dark = syncPack('pack-dark', true);
    $user = syncUser(['resource_pack_id' => $light->id]);

    $this->actingAs($user)
        ->putJson(route('user.resource-pack.update'), ['resource_pack_id' => $dark->id])
        ->assertSuccessful();

    $this->actingAs($user->fresh())
        ->put(route('dark-mode.update'), ['dark_mode' => false])
        ->assertRedirect();

    expect($user->fresh()->dark_mode)->toBeFalse();

    $this->actingAs($user->fresh())
        ->get(route('announcements.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('dark_mode', false));
});

it('drops the toggle when the member switches to the untextured interface', function () {
    $dark = syncPack('pack-dark', true);
    $user = syncUser(['resource_pack_id' => $dark->id, 'dark_mode' => true]);

    $this->actingAs($user)
        ->putJson(route('user.resource-pack.update'), ['resource_pack_id' => ResourcePack::GUEST_NO_PACK])
        ->assertSuccessful();

    expect($user->fresh()->dark_mode)->toBeNull();
    expect($user->fresh()->disable_resource_pack)->toBeTrue();
});

it('still lets the owner instance default override the pack flag after a pack change', function () {
    // The instance default sits between the user toggle and the pack flag, and
    // exists precisely to overrule unreliable pack flags. Clearing the toggle
    // must not promote the pack past it.
    SettingHelper::setSetting('default_dark_mode', 'light');
    $light = syncPack('pack-light', false);
    $dark = syncPack('pack-dark', true);
    $user = syncUser(['resource_pack_id' => $light->id, 'dark_mode' => true]);

    $this->actingAs($user)
        ->putJson(route('user.resource-pack.update'), ['resource_pack_id' => $dark->id])
        ->assertSuccessful();

    $this->actingAs($user->fresh())
        ->get(route('announcements.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('dark_mode', false));
});

it('keeps Vanilla on its single fixed scheme with no toggle after a pack change', function () {
    $vanilla = syncPack(ResourcePack::VANILLA, false);
    $dark = syncPack('pack-dark', true);
    $user = syncUser(['resource_pack_id' => $dark->id, 'dark_mode' => true]);

    $this->actingAs($user)
        ->putJson(route('user.resource-pack.update'), ['resource_pack_id' => $vanilla->id])
        ->assertSuccessful();

    $this->actingAs($user->fresh())
        ->get(route('announcements.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('can_toggle_dark_mode', false)
            ->where('dark_mode', false)
            ->where('theme', Instance::THEME_VANILLA),
        );
});

it('stamps the new pack theme on the server-rendered html, so first paint cannot flash', function () {
    // The Appearance page full-reloads after saving; the theme therefore has to
    // be right in the Blade root, not patched in by the client after hydration.
    $light = syncPack('pack-light', false);
    $dark = syncPack('pack-dark', true);
    $user = syncUser(['resource_pack_id' => $light->id, 'dark_mode' => false]);

    $this->actingAs($user)
        ->putJson(route('user.resource-pack.update'), ['resource_pack_id' => $dark->id])
        ->assertSuccessful();

    $this->actingAs($user->fresh())
        ->get(route('announcements.index'))
        ->assertSee('data-theme="'.Instance::THEME_DARK.'"', false)
        ->assertSee('class="dark has-pack"', false);
});

it('clears a guest light/dark cookie when they change pack', function () {
    syncPack(ResourcePack::VANILLA, false);
    $dark = syncPack('pack-dark', true);

    // withCredentials so the JSON request carries the cookies a browser would.
    $this->withCredentials()
        ->withUnencryptedCookie(Instance::DARK_MODE_COOKIE, '0')
        ->putJson(route('user.resource-pack.update'), ['resource_pack_id' => $dark->id])
        ->assertOk()
        ->assertCookie(Instance::RESOURCE_PACK_COOKIE, (string) $dark->id, encrypted: false)
        ->assertCookieExpired(Instance::DARK_MODE_COOKIE);
});

it('leaves a guest light/dark cookie alone when they re-pick the same pack', function () {
    $dark = syncPack('pack-dark', true);

    $this->withCredentials()
        ->withUnencryptedCookie(Instance::RESOURCE_PACK_COOKIE, (string) $dark->id)
        ->withUnencryptedCookie(Instance::DARK_MODE_COOKIE, '0')
        ->putJson(route('user.resource-pack.update'), ['resource_pack_id' => $dark->id])
        ->assertOk()
        // Untouched: the response carries no dark_mode cookie at all, so the
        // one the browser already holds survives.
        ->assertCookieMissing(Instance::DARK_MODE_COOKIE);
});

it('serves a guest the dark pack scheme once their stale cookie is gone', function () {
    $dark = syncPack('pack-dark', true);

    $this->withUnencryptedCookie(Instance::RESOURCE_PACK_COOKIE, (string) $dark->id)
        ->get(route('announcements.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('dark_mode', true)
            ->where('theme', Instance::THEME_DARK),
        );
});

it('does not clear the toggle when the plugin re-pushes the pack already in effect', function () {
    // The RuneLite plugin mirrors the in-game pack on every push; that must not
    // wipe a preference the member set on the website.
    $pack = syncPack('pack-dark', true);
    $user = syncUser(['resource_pack_id' => $pack->id, 'dark_mode' => false]);

    $this->partialMock(InstallResourcePack::class, fn ($m) => $m->shouldReceive('isInstalled')->andReturn(true));
    Sanctum::actingAs($user);

    $this->putJson('/api/plugin/resource-pack', ['name' => 'pack-dark'])->assertStatus(200);

    expect($user->fresh()->dark_mode)->toBeFalse();
});

it('clears the toggle when the plugin pushes a different in-game pack', function () {
    $light = syncPack('pack-light', false);
    syncPack('pack-dark', true);
    $user = syncUser(['resource_pack_id' => $light->id, 'dark_mode' => false]);

    $this->partialMock(InstallResourcePack::class, fn ($m) => $m->shouldReceive('isInstalled')->andReturn(true));
    Sanctum::actingAs($user);

    $this->putJson('/api/plugin/resource-pack', ['name' => 'pack-dark'])->assertStatus(200);

    expect($user->fresh()->dark_mode)->toBeNull();
});
