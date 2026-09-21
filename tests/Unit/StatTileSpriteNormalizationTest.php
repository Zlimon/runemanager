<?php

use App\Services\ResourcePacks\InstallResourcePack;

/**
 * Stat tiles are drawn as two halves painted at 50% of the cell each, so the
 * art has to be flush with the edges of its own PNG. Packs pad theirs
 * differently — one pack's left half starts two pixels in, another's four —
 * and those margins used to meet in the middle as a transparent gap.
 */
function tileFixture(int $width, int $height, int $artLeft, int $artRight): string
{
    $path = tempnam(sys_get_temp_dir(), 'tile').'.png';
    $image = imagecreatetruecolor($width, $height);
    imagealphablending($image, false);
    imagesavealpha($image, true);
    imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, imagecolorallocatealpha($image, 0, 0, 0, 127));
    imagefilledrectangle($image, $artLeft, 0, $artRight, $height - 1, imagecolorallocatealpha($image, 200, 40, 90, 0));
    imagepng($image, $path);
    imagedestroy($image);

    return $path;
}

function cropTile(string $fixture, string $half): array
{
    $dir = sys_get_temp_dir().'/pack-'.uniqid();
    mkdir($dir.'/stats', 0777, true);
    copy($fixture, $dir."/stats/tile_half_{$half}.png");

    app(InstallResourcePack::class)->normalizeStatTileSprites($dir);

    $size = getimagesize($dir."/stats/tile_half_{$half}.png");

    unlink($dir."/stats/tile_half_{$half}.png");
    rmdir($dir.'/stats');
    rmdir($dir);
    unlink($fixture);

    return $size;
}

it('trims the transparent margins so the art is flush with the canvas', function () {
    // The common shape: 31px of art padded into a 36px canvas.
    [$width, $height] = cropTile(tileFixture(36, 36, 2, 32), 'left');

    expect($width)->toBe(31)
        ->and($height)->toBe(36);
});

it('handles a pack whose art sits at a different offset', function () {
    // A pack whose left half starts four pixels in, on a 35px canvas.
    [$width, $height] = cropTile(tileFixture(35, 36, 4, 33), 'left');

    expect($width)->toBe(30)
        ->and($height)->toBe(36);
});

it('is idempotent once the art already fills the canvas', function () {
    [$width] = cropTile(tileFixture(31, 36, 0, 30), 'right');

    expect($width)->toBe(31);
});

it('leaves a fully transparent sprite alone rather than cropping it to nothing', function () {
    [$width, $height] = cropTile(tileFixture(36, 36, 40, 40), 'right');

    expect($width)->toBe(36)
        ->and($height)->toBe(36);
});

it('keeps full height, trimming horizontally only', function () {
    // The halves are already full-bleed vertically; the CSS stretches height.
    [$width, $height] = cropTile(tileFixture(35, 37, 2, 32), 'right');

    expect($width)->toBe(31)
        ->and($height)->toBe(37);
});
