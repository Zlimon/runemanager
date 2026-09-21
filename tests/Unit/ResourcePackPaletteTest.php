<?php

use App\Models\ResourcePack;
use App\Services\ResourcePacks\PackPalette;
use Illuminate\Support\Facades\File;

/**
 * Build a throwaway pack directory whose dialog background is one flat color,
 * optionally shipping a `color.properties` alongside it.
 */
function palettePackDir(string $background, ?string $properties = null, ?string $packName = null): string
{
    // The directory name is the pack name, which the default pack is singled
    // out by — so tests that care about that pass one.
    $dir = sys_get_temp_dir().'/palette_'.uniqid().($packName === null ? '' : '/'.$packName);
    File::ensureDirectoryExists($dir.'/dialog');

    [$r, $g, $b] = sscanf($background, '#%02x%02x%02x');
    $image = imagecreatetruecolor(16, 16);
    imagefill($image, 0, 0, imagecolorallocate($image, $r, $g, $b));
    imagepng($image, $dir.'/dialog/background.png');
    imagedestroy($image);

    if ($properties !== null) {
        File::put($dir.'/color.properties', $properties);
    }

    return $dir;
}

/**
 * @return array{background_color: ?string, accent_color: ?string}
 */
function palette(string $background, ?string $properties = null, ?string $packName = null): array
{
    $dir = palettePackDir($background, $properties, $packName);

    try {
        return (new PackPalette)->extractPaletteColors($dir);
    } finally {
        File::deleteDirectory($packName === null ? $dir : dirname($dir));
    }
}

/**
 * @return array{int, int, int}
 */
function channels(string $hex): array
{
    return sscanf($hex, '#%02x%02x%02x');
}

function perceivedLuminance(string $hex): float
{
    [$r, $g, $b] = channels($hex);

    return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
}

/**
 * HSL hue in degrees, for asserting that a shifted colour kept its hue.
 */
function hue(string $hex): float
{
    [$r, $g, $b] = array_map(fn (int $c): float => $c / 255, channels($hex));
    $max = max($r, $g, $b);
    $delta = $max - min($r, $g, $b);

    if ($delta <= 0.0) {
        return 0.0;
    }

    return 60 * match ($max) {
        $r => fmod((($g - $b) / $delta) + 6, 6),
        $g => (($b - $r) / $delta) + 2,
        default => (($r - $g) / $delta) + 4,
    };
}

it('keeps the hue of a saturated dark pack in its accent', function () {
    // A saturated dark purple background. The accent has to stay purple:
    // blending toward white (the old derivation) returned a grey #6e607c.
    $palette = palette('#200b36');

    expect($palette['background_color'])->toBe('#200b36');

    [$r, $g, $b] = channels($palette['accent_color']);

    expect($b)->toBeGreaterThan($r)
        ->and($r)->toBeGreaterThan($g * 2)
        ->and($b)->toBeGreaterThan($g * 2);
});

it('keeps the hue of a saturated dark teal pack in its accent', function () {
    // A deep teal background, which the old derivation flattened to grey.
    [$r, $g, $b] = channels(palette('#031a24')['accent_color']);

    expect($b)->toBeGreaterThan($g)
        ->and($g)->toBeGreaterThan($r * 2);
});

it('leaves a greyscale pack grey', function () {
    // A genuinely grey background, so the accent must be grey too.
    $accent = palette('#2f2c2e')['accent_color'];

    [$r, $g, $b] = channels($accent);

    expect(max($r, $g, $b) - min($r, $g, $b))->toBeLessThanOrEqual(6);
});

it('treats a near-black background with a token colour cast as grey', function () {
    // Black to the eye, but fully saturated blue
    // in HSL. Amplifying that hue would paint the pack with vivid blue borders.
    [$r, $g, $b] = channels(palette('#000009')['accent_color']);

    expect(max($r, $g, $b) - min($r, $g, $b))->toBeLessThanOrEqual(6);
});

it('lightens the accent for a dark pack and darkens it for a light one', function () {
    $dark = palette('#200b36');
    $light = palette('#e8e0d0');

    expect(perceivedLuminance($dark['accent_color']))
        ->toBeGreaterThan(perceivedLuminance($dark['background_color']));

    expect(perceivedLuminance($light['accent_color']))
        ->toBeLessThan(perceivedLuminance($light['background_color']));
});

it("prefers the pack's own declared accent from color.properties", function () {
    $palette = palette('#200b36', <<<'PROPERTIES'
        # 8 character hex code (first 2 are alpha, next 2 red, then green and blue)
        chatbox_seperator_line=0x8000ff
        world_switch_top_and_bottom_lines=0x802bc4
        PROPERTIES);

    expect($palette['accent_color'])->toBe('#802bc4');
});

it('falls through to the next declared key when the first is unusable', function () {
    // world_switch_* sits right on top of the background, bank_tab_underline does not.
    $palette = palette('#200b36', <<<'PROPERTIES'
        world_switch_top_and_bottom_lines=0x210c37
        bank_tab_underline=0x802bc4
        PROPERTIES);

    expect($palette['accent_color'])->toBe('#802bc4');
});

it('reads short-form declared values as zero-padded RGB', function () {
    // Leading zeros are stripped upstream: 0x98a0 means #0098a0.
    expect(palette('#200b36', 'world_switch_top_and_bottom_lines=0x98a0')['accent_color'])
        ->toBe('#0098a0');
});

it('reads 8-digit declared values as alpha-first ARGB', function () {
    expect(palette('#200b36', 'world_switch_top_and_bottom_lines=0x9c802bc4')['accent_color'])
        ->toBe('#802bc4');

    // Same, with the alpha's own leading zero stripped (7 digits).
    expect(palette('#200b36', 'world_switch_top_and_bottom_lines=0x9802bc4')['accent_color'])
        ->toBe('#802bc4');
});

it('ignores a declared accent that barely separates from the background', function () {
    // A real case: a pack whose declared line colours are nearly its background.
    $declared = palette('#2f2c2e', <<<'PROPERTIES'
        world_switch_top_and_bottom_lines=0x3f3f3f
        bank_tab_underline=0x38373c
        PROPERTIES);

    expect($declared['accent_color'])->toBe(palette('#2f2c2e')['accent_color']);
});

it('ignores a declared accent too faint to read as a deliberate line', function () {
    // sample-vanilla's declared OSRS tan reaches only 1.81:1 on its own
    // background — below that an edge reads as a rendering fault, not a border.
    $declared = palette('#483f33', 'world_switch_top_and_bottom_lines=0x73654a');

    expect(PackPalette::contrastRatio('#73654a', '#483f33'))->toBeLessThan(2.0)
        ->and($declared['accent_color'])->toBe(palette('#483f33')['accent_color']);
});

it('keeps a declared accent that reads as a line but falls short of the control bar', function () {
    // A real case: #5b5b5b is visibly a line at 2.03:1, and is kept
    // as the authentic accent even though it cannot carry a control on its own.
    // uiAccent() is what makes that safe.
    $declared = palette('#2f2c2e', 'chatbox_seperator_line=0x5b5b5b');

    expect(PackPalette::contrastRatio('#5b5b5b', '#2f2c2e'))
        ->toBeGreaterThanOrEqual(2.0)
        ->toBeLessThan(3.0);

    expect($declared['accent_color'])->toBe('#5b5b5b')
        ->and(PackPalette::contrastRatio(
            (new PackPalette)->uiAccent('#5b5b5b', '#2f2c2e'), '#2f2c2e',
        ))->toBeGreaterThanOrEqual(3.0);
});

it('holds the bundled default pack to the derived accent, declared or not', function () {
    // Every user who has never picked a pack sees this one, so it does not take
    // an authentic-but-fainter colour the way a pack someone opted into does.
    $properties = 'chatbox_seperator_line=0x807660';

    $default = palette('#483f33', $properties, 'sample-vanilla');
    $optedInto = palette('#483f33', $properties, 'pack-something-else');

    expect($default['accent_color'])->toBe(palette('#483f33')['accent_color'])
        ->not->toBe('#807660');

    // The same declared colour is honoured for any other pack.
    expect($optedInto['accent_color'])->toBe('#807660');
});

it('ignores a malformed color.properties without throwing', function (string $properties) {
    expect(palette('#200b36', $properties)['accent_color'])
        ->toBe(palette('#200b36')['accent_color']);
})->with([
    'binary junk' => "\x00\x01\x02not a properties file at all",
    'no value' => 'world_switch_top_and_bottom_lines=',
    'not hex' => 'world_switch_top_and_bottom_lines=0xnothex',
    'too long' => 'world_switch_top_and_bottom_lines=0x1234567890',
    'commented out' => '#world_switch_top_and_bottom_lines=0x802bc4',
    'unrelated keys only' => "overlay_color=0x9c8000ff\nachievement_diary_border=0xffffff",
    'empty' => '',
]);

it('returns nulls when the pack ships no sampleable background', function () {
    $dir = sys_get_temp_dir().'/palette_empty_'.uniqid();
    File::ensureDirectoryExists($dir);

    try {
        expect((new PackPalette)->extractPaletteColors($dir))
            ->toBe(['background_color' => null, 'accent_color' => null]);
    } finally {
        File::deleteDirectory($dir);
    }
});

it('computes WCAG contrast ratios, not raw channel gaps', function (string $a, string $b, float $expected) {
    expect(PackPalette::contrastRatio($a, $b))->toBeGreaterThan($expected - 0.01)
        ->and(PackPalette::contrastRatio($a, $b))->toBeLessThan($expected + 0.01);
})->with([
    'black on white' => ['#000000', '#ffffff', 21.0],
    'white on black (symmetric)' => ['#ffffff', '#000000', 21.0],
    'identical colours' => ['#483f33', '#483f33', 1.0],
    'mid grey on white' => ['#777777', '#ffffff', 4.48],
    'mid grey on black' => ['#808080', '#000000', 5.32],
    // The case the raw-channel metric got wrong: a saturated purple reads as
    // far lighter than it is until each channel is linearized.
    'saturated purple on its own dark background' => ['#802bc4', '#200b36', 2.63],
]);

it('returns the accent untouched when it already clears the contrast bar', function () {
    // A derived teal, well past 3:1 on its background.
    expect((new PackPalette)->uiAccent('#038dc9', '#031a24'))->toBe('#038dc9');
});

it('lifts an accent that falls short of the contrast bar', function () {
    $accent = '#802bc4';
    $background = '#200b36';

    expect(PackPalette::contrastRatio($accent, $background))->toBeLessThan(3.0);

    $ui = (new PackPalette)->uiAccent($accent, $background);

    expect($ui)->not->toBe($accent)
        ->and(PackPalette::contrastRatio($ui, $background))->toBeGreaterThanOrEqual(3.0);
});

it('keeps the hue when it lifts an accent', function () {
    $ui = (new PackPalette)->uiAccent('#802bc4', '#200b36');

    expect(abs(hue($ui) - hue('#802bc4')))->toBeLessThan(2.0);

    // Still recognisably purple, not washed toward white.
    [$r, $g, $b] = channels($ui);
    expect($b)->toBeGreaterThan($r)
        ->and($r)->toBeGreaterThan($g * 2);
});

it('lifts every pack accent to a usable control colour', function (string $background) {
    $accent = palette($background)['accent_color'];
    $ui = (new PackPalette)->uiAccent($accent, $background);

    expect(PackPalette::contrastRatio($ui, $background))->toBeGreaterThanOrEqual(3.0);
})->with(['#200b36', '#031a24', '#2f2c2e', '#000009', '#483f33', '#36312d', '#ffffff', '#000000']);

it('derives an accent that already clears the contrast bar', function (string $background) {
    // Nothing we derive ourselves should need lifting — uiAccent only exists
    // for the authentic colours packs declare.
    $accent = palette($background)['accent_color'];

    expect(PackPalette::contrastRatio($accent, $background))->toBeGreaterThanOrEqual(3.0);
})->with(['#200b36', '#031a24', '#2f2c2e', '#000009', '#483f33', '#36312d', '#ffffff', '#000000']);

it('darkens rather than lightens when the background is light', function () {
    $ui = (new PackPalette)->uiAccent('#d8d0c0', '#e8e0d0');

    expect(perceivedLuminance($ui))->toBeLessThan(perceivedLuminance('#e8e0d0'))
        ->and(PackPalette::contrastRatio($ui, '#e8e0d0'))->toBeGreaterThanOrEqual(3.0);
});

it('exposes the ui accent on the model, null-safe', function () {
    $pack = new ResourcePack;

    $pack->background_color = null;
    $pack->accent_color = null;
    expect($pack->accent_ui_color)->toBeNull();

    $pack->background_color = '#200b36';
    expect($pack->accent_ui_color)->toBeNull();

    $pack->accent_color = '#802bc4';
    expect($pack->accent_ui_color)->toBe((new PackPalette)->uiAccent('#802bc4', '#200b36'));

    $pack->background_color = null;
    expect($pack->accent_ui_color)->toBeNull();
});
