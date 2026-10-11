<?php

use App\Models\ResourcePack;
use App\Services\ResourcePacks\InstallResourcePack;
use App\Services\ResourcePacks\PackPalette;
use App\Support\Roles;
use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia;

/**
 * The suite runs with APP_ENV=testing, so these exercise the gated branch of the
 * route's middleware — the one that ships. The open-in-local branch is chosen at
 * route-registration time and can't be flipped mid-request.
 */
function styleguidePack(string $name, bool $darkMode = false): ResourcePack
{
    return ResourcePack::query()->forceCreate([
        'name' => $name,
        'alias' => ucfirst($name),
        'version' => '1.0.0',
        'author' => 'test',
        'url' => "https://example.test/{$name}.zip",
        'tags' => '',
        'dark_mode' => $darkMode,
        'background_color' => '#123456',
        'accent_color' => '#abcdef',
    ]);
}

afterEach(function () {
    File::deleteDirectory(public_path('resource-packs/pack-styleguidetest'));
});

it('lifts the payload accent for a pack whose accent is too faint for controls', function () {
    // A real saturated palette: the authentic purple is kept as-is, and the
    // switcher gets a separate, contrast-safe version for form controls.
    styleguidePack('pack-styleguidetest', true)
        ->forceFill(['background_color' => '#200b36', 'accent_color' => '#802bc4'])
        ->save();

    $this->actingAs(adminUser())
        ->get(route('styleguide'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('packs', 1)
            ->where('packs.0.accent_color', '#802bc4')
            ->where('packs.0.accent_ui_color', fn (?string $ui) => $ui !== '#802bc4'
                && PackPalette::contrastRatio((string) $ui, '#200b36') >= 3.0)
        );
});

it('redirects guests away from the styleguide outside local', function () {
    $this->get(route('styleguide'))->assertRedirect(route('login'));
});

it('forbids a user without manage instance', function () {
    $this->actingAs(adminUser(Roles::USER))
        ->get(route('styleguide'))
        ->assertForbidden();
});

it('lists every installed pack with what the switcher needs to repaint the page', function () {
    styleguidePack(InstallResourcePack::VANILLA_PACK, false);
    styleguidePack('pack-styleguidetest', true);

    $this->actingAs(adminUser())
        ->get(route('styleguide'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Styleguide/Index')
            ->has('packs', 2)
            ->has('items')
            // Ordered by alias: "Pack-styleguidetest" before "Sample-vanilla".
            ->where('packs.0.name', 'pack-styleguidetest')
            ->where('packs.0.dark_mode', true)
            ->where('packs.0.background_color', '#123456')
            ->where('packs.0.accent_color', '#abcdef')
            // Derived on read, and left alone here because #abcdef already
            // clears the contrast bar against #123456 (7.70:1).
            ->where('packs.0.accent_ui_color', '#abcdef')
            ->where('packs.0.is_vanilla', false)
            // Only vanilla is actually installed on disk, so it is the only one
            // with a stylesheet to link. A pack row without files must still be
            // listed — the switcher renders it as the untextured interface
            // rather than pointing <link> at a 404.
            ->where('packs.0.css', null)
            // The picker shape rides along, so the pack components on the page
            // render with each pack's own id, icon and details.
            ->where('packs.0.id', fn (int $id) => $id > 0)
            ->has('packs.0.icon_url')
            ->has('packs.0.tags')
            ->where('packs.1.is_vanilla', true)
            ->where('packs.1.css', fn (?string $css) => str_contains((string) $css, 'sample-vanilla/resource-pack.css'))
        );
});

it('separates the sprites a pack ships from the ones it borrowed from vanilla', function () {
    styleguidePack('pack-styleguidetest');

    $dir = public_path('resource-packs/pack-styleguidetest/button');
    File::ensureDirectoryExists($dir);

    // Byte-identical to vanilla's = copied in by completeFromVanilla() at
    // install time, which is what "borrowed" means.
    $vanillaSprite = public_path(
        'resource-packs/'.InstallResourcePack::VANILLA_PACK.'/button/corner_top_left.png',
    );
    File::copy($vanillaSprite, $dir.'/corner_top_left.png');
    File::put($dir.'/edge_top.png', 'this pack drew its own edge');

    $response = $this->actingAs(adminUser())
        ->getJson(route('styleguide.sprites', ['pack' => 'pack-styleguidetest']))
        ->assertOk();

    expect($response->json('own'))->toBe(1);
    expect($response->json('borrowed'))->toBe(1);

    $byPath = collect($response->json('files'))->keyBy('path');
    expect($byPath['button/edge_top.png']['own'])->toBeTrue();
    expect($byPath['button/corner_top_left.png']['own'])->toBeFalse();
    // Dimensions come from the file itself, so a malformed sprite reports 0
    // rather than blowing up the whole inventory.
    expect($byPath['button/edge_top.png']['width'])->toBe(0);
    expect($byPath['button/corner_top_left.png']['width'])->toBeGreaterThan(0);
});

it('treats vanilla as owning everything it ships', function () {
    $response = $this->actingAs(adminUser())
        ->getJson(route('styleguide.sprites', ['pack' => InstallResourcePack::VANILLA_PACK]))
        ->assertOk();

    expect($response->json('borrowed'))->toBe(0);
    expect($response->json('own'))->toBeGreaterThan(0);
});

it('returns an empty inventory for a pack that is not on disk', function () {
    $response = $this->actingAs(adminUser())
        ->getJson(route('styleguide.sprites', ['pack' => 'pack-never-installed']))
        ->assertOk();

    expect($response->json('pack'))->toBeNull();
    expect($response->json('files'))->toBe([]);
});
