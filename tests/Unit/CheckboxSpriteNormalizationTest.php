<?php

use App\Services\ResourcePacks\InstallResourcePack;

/**
 * Packs ship the checkbox faces as a 36x36 canvas with the glyph in the
 * top-left corner, the glyph measuring somewhere around 14-17px. The CSS
 * paints them with `contain`, so that padding was scaled into the control and
 * the box rendered at under half size, up and to the left of centre.
 */
function checkboxFixture(int $width, int $height, int $x0, int $y0, int $x1, int $y1): string
{
    $path = tempnam(sys_get_temp_dir(), 'cb').'.png';
    $image = imagecreatetruecolor($width, $height);
    imagealphablending($image, false);
    imagesavealpha($image, true);
    imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, imagecolorallocatealpha($image, 0, 0, 0, 127));
    imagefilledrectangle($image, $x0, $y0, $x1, $y1, imagecolorallocatealpha($image, 220, 30, 30, 0));
    imagepng($image, $path);
    imagedestroy($image);

    return $path;
}

function cropCheckbox(string $fixture, string $name = 'checkbox_empty'): array
{
    $dir = sys_get_temp_dir().'/pack-'.uniqid();
    mkdir($dir.'/options', 0777, true);
    copy($fixture, $dir."/options/{$name}.png");

    app(InstallResourcePack::class)->normalizeCheckboxSprites($dir);

    $size = getimagesize($dir."/options/{$name}.png");

    unlink($dir."/options/{$name}.png");
    rmdir($dir.'/options');
    rmdir($dir);
    unlink($fixture);

    return $size;
}

it('trims the empty canvas around a corner-anchored glyph', function () {
    // The common shape: a 14x15 glyph in the corner of a 36x36 canvas.
    [$width, $height] = cropCheckbox(checkboxFixture(36, 36, 0, 0, 13, 14));

    expect($width)->toBe(14)
        ->and($height)->toBe(15);
});

it('trims on both axes, unlike the stat tiles', function () {
    // Offset on both axes: vertical padding has to go too, or `contain` still
    // pushes the glyph off centre.
    [$width, $height] = cropCheckbox(checkboxFixture(36, 36, 4, 6, 20, 22));

    expect($width)->toBe(17)
        ->and($height)->toBe(17);
});

it('crops the checked face as well as the empty one', function () {
    [$width, $height] = cropCheckbox(checkboxFixture(36, 36, 0, 0, 16, 16), 'checkbox_checked_red');

    expect($width)->toBe(17)
        ->and($height)->toBe(17);
});

it('is idempotent once the glyph already fills the canvas', function () {
    [$width, $height] = cropCheckbox(checkboxFixture(14, 15, 0, 0, 13, 14));

    expect($width)->toBe(14)
        ->and($height)->toBe(15);
});

it('leaves a fully transparent sprite alone rather than cropping it to nothing', function () {
    [$width, $height] = cropCheckbox(checkboxFixture(36, 36, 40, 40, 41, 41));

    expect($width)->toBe(36)
        ->and($height)->toBe(36);
});
