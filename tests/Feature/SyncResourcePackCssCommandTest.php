<?php

use App\Services\ResourcePacks\InstallResourcePack;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

/**
 * Create an installed-looking pack directory holding the given stylesheet.
 */
function installedPackDir(string $name, string $css): string
{
    $dir = public_path("resource-packs/{$name}");
    File::ensureDirectoryExists($dir);
    File::put($dir.'/resource-pack.css', $css);

    return $dir;
}

afterEach(function () {
    foreach (['pack-synctest-stale', 'pack-synctest-current', 'pack-synctest-partial'] as $name) {
        File::deleteDirectory(public_path("resource-packs/{$name}"));
    }
});

/**
 * Give the pack a button face of the given dimensions — the one sprite that
 * decides how the template renders for it.
 */
function packFace(string $dir, int $width, int $height): void
{
    File::ensureDirectoryExists($dir.'/button');
    $image = imagecreatetruecolor($width, $height);
    imagepng($image, $dir.'/button/middle.png');
    imagedestroy($image);
}

it('rewrites a pack whose stylesheet has drifted from the template', function () {
    $dir = installedPackDir('pack-synctest-stale', '/* an older copy */');

    $result = app(InstallResourcePack::class)->syncTemplateCss();

    expect($result['updated'])->toContain('pack-synctest-stale');
    expect(File::get($dir.'/resource-pack.css'))
        ->toBe(File::get(resource_path('css/per-pack.css')));
});

it('leaves a pack alone when its stylesheet already matches', function () {
    $template = File::get(resource_path('css/per-pack.css'));
    $dir = installedPackDir('pack-synctest-current', $template);
    // Backdate it: lastModified() has second granularity, so a same-second
    // rewrite would otherwise be indistinguishable from an untouched file.
    $before = now()->subDay()->getTimestamp();
    touch($dir.'/resource-pack.css', $before);

    $result = app(InstallResourcePack::class)->syncTemplateCss();

    expect($result['unchanged'])->toContain('pack-synctest-current');
    expect($result['updated'])->not->toContain('pack-synctest-current');
    // app.blade.php cache-busts the <link> on mtime, so an identical rewrite
    // would needlessly expire every viewer's cached stylesheet.
    expect(File::lastModified($dir.'/resource-pack.css'))->toBe($before);
});

it('skips a directory that was never fully installed', function () {
    $dir = public_path('resource-packs/pack-synctest-partial');
    File::ensureDirectoryExists($dir);

    $result = app(InstallResourcePack::class)->syncTemplateCss();

    expect($result['updated'])->not->toContain('pack-synctest-partial');
    expect($result['unchanged'])->not->toContain('pack-synctest-partial');
    expect(File::exists($dir.'/resource-pack.css'))->toBeFalse();
});

it('borrows a sprite the template newly references but the pack never shipped', function () {
    $template = File::get(resource_path('css/per-pack.css'));
    $dir = installedPackDir('pack-synctest-stale', $template);
    // The pack ships one corner of the button frame and nothing else, like a
    // partial pack installed before the template grew a new url() reference.
    File::ensureDirectoryExists($dir.'/button');
    File::put($dir.'/button/corner_top_left.png', 'this pack\'s own corner');

    $result = app(InstallResourcePack::class)->syncTemplateCss();

    expect($result['filled'])->toBeGreaterThan(0);
    expect(File::exists($dir.'/button/edge_bottom.png'))->toBeTrue();
    // A sprite the pack does ship is never overwritten with vanilla's.
    expect(File::get($dir.'/button/corner_top_left.png'))->toBe('this pack\'s own corner');
});

it('tiles the button face for a pack that ships a small seamless swatch', function () {
    $dir = installedPackDir('pack-synctest-stale', '/* an older copy */');
    packFace($dir, 12, 12);

    app(InstallResourcePack::class)->syncTemplateCss();

    expect(File::get($dir.'/resource-pack.css'))
        ->toContain('--pack-face-repeat: repeat;')
        ->toContain('--pack-face-size: auto;');
});

it('stretches the button face for a pack that ships a whole pre-rendered face', function () {
    $dir = installedPackDir('pack-synctest-stale', '/* an older copy */');
    // Larger than a tileable swatch: repeated across a wide button its joins
    // would show as the hard vertical seams that read as a panel in the frame.
    packFace($dir, 56, 56);

    app(InstallResourcePack::class)->syncTemplateCss();

    expect(File::get($dir.'/resource-pack.css'))
        ->toContain('--pack-face-repeat: no-repeat;')
        ->toContain('--pack-face-size: 100% 100%;');
});

it('leaves a stretched pack alone on a second run', function () {
    $dir = installedPackDir('pack-synctest-stale', '/* an older copy */');
    packFace($dir, 56, 56);

    $installer = app(InstallResourcePack::class);
    $installer->syncTemplateCss();
    // Comparing against the raw template rather than this pack's own render
    // would rewrite it every run, expiring cached stylesheets for no change.
    $result = $installer->syncTemplateCss();

    expect($result['unchanged'])->toContain('pack-synctest-stale');
    expect($result['updated'])->not->toContain('pack-synctest-stale');
});

it('reports what it refreshed', function () {
    installedPackDir('pack-synctest-stale', '/* an older copy */');

    $this->artisan('resourcepack:sync-css')
        ->expectsOutputToContain('refreshed pack-synctest-stale')
        ->assertSuccessful();
});

it('never fails the deploy when the template is unreadable', function () {
    $this->mock(InstallResourcePack::class, function ($mock) {
        $mock->shouldReceive('syncTemplateCss')->andThrow(new RuntimeException('template missing'));
    });

    $this->artisan('resourcepack:sync-css')
        ->expectsOutputToContain('Could not sync per-pack CSS')
        ->assertSuccessful();
});
