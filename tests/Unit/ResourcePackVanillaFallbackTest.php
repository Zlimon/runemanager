<?php

use App\Services\ResourcePacks\InstallResourcePack;
use Illuminate\Support\Facades\File;

function tempPackDir(string $prefix): string
{
    $dir = sys_get_temp_dir().'/'.$prefix.'_'.uniqid();
    File::ensureDirectoryExists($dir);

    return $dir;
}

it('fills in only the assets a pack is missing, from vanilla, without overwriting', function () {
    $vanilla = tempPackDir('vanilla');
    $pack = tempPackDir('pack');

    // Vanilla ships all three; the pack only ships its own dialog background.
    File::ensureDirectoryExists($vanilla.'/other');
    File::ensureDirectoryExists($vanilla.'/dialog');
    File::put($vanilla.'/other/minimap_orb_hitpoints.png', 'VANILLA-ORB');
    File::put($vanilla.'/other/minimap_orb_prayer.png', 'VANILLA-PRAYER');
    File::put($vanilla.'/dialog/background.png', 'VANILLA-BG');

    File::ensureDirectoryExists($pack.'/dialog');
    File::put($pack.'/dialog/background.png', 'PACK-BG');

    $copied = InstallResourcePack::fillMissingAssets($pack, $vanilla, [
        'other/minimap_orb_hitpoints.png',
        'other/minimap_orb_prayer.png',
        'dialog/background.png',
        'other/does_not_exist_anywhere.png',
    ]);

    // The two missing orb sprites were copied; the pack's own background was kept.
    expect($copied)->toBe(2);
    expect(File::get($pack.'/other/minimap_orb_hitpoints.png'))->toBe('VANILLA-ORB');
    expect(File::get($pack.'/other/minimap_orb_prayer.png'))->toBe('VANILLA-PRAYER');
    expect(File::get($pack.'/dialog/background.png'))->toBe('PACK-BG');
    expect(File::exists($pack.'/other/does_not_exist_anywhere.png'))->toBeFalse();

    File::deleteDirectory($vanilla);
    File::deleteDirectory($pack);
});

it('reads the per-pack CSS referenced assets (e.g. the orb sprites)', function () {
    $assets = (new InstallResourcePack)->referencedAssets();

    expect($assets)->toContain('other/minimap_orb_hitpoints.png')
        ->toContain('other/minimap_orb_special.png');
});

it('borrows frontend-addressed equipment + skill sprites a pack omits', function () {
    // An otherwise-empty pack borrows everything from the bundled vanilla — the
    // equipment slots and skill icons the frontend loads directly by pack name.
    $pack = tempPackDir('imgpack');

    $copied = (new InstallResourcePack)->completeFromVanilla($pack);

    expect($copied)->toBeGreaterThan(0);
    expect(File::exists($pack.'/equipment/slot_ammunition.png'))->toBeTrue();
    expect(File::exists($pack.'/skill/attack.png'))->toBeTrue();

    File::deleteDirectory($pack);
});

it('does not fill the vanilla pack from itself', function () {
    expect((new InstallResourcePack)->completeFromVanilla(public_path('resource-packs/sample-vanilla')))
        ->toBe(0);
});

it('derives horizontal scrollbar sprites by rotating the vertical ones', function () {
    $pack = tempPackDir('scroll');
    File::ensureDirectoryExists($pack.'/scrollbar');

    // A 16x5 vertical track sprite (as packs ship).
    $img = imagecreatetruecolor(16, 5);
    imagepng($img, $pack.'/scrollbar/thumb_middle_dark.png');
    imagedestroy($img);

    (new InstallResourcePack)->generateHorizontalScrollbar($pack);

    [$width, $height] = getimagesize($pack.'/scrollbar/horizontal_thumb_middle_dark.png');
    expect($width)->toBe(5)->and($height)->toBe(16);

    File::deleteDirectory($pack);
});
