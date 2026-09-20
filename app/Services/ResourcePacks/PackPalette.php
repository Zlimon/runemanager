<?php

namespace App\Services\ResourcePacks;

/**
 * Derive the two-colour palette a resource pack is presented with: a background
 * sampled from the pack's own sprites, and an accent that reads against it as a
 * border or outline. The pair lands on `resource_packs.background_color` /
 * `.accent_color` and is emitted as the `--pack-bg` / `--pack-accent` CSS
 * variables, from which every further shade is derived in CSS.
 *
 * The accent is the pack author's own declared line colour where the pack ships
 * a usable one, and otherwise a hue-preserving shift of the background. Packs
 * are untrusted input throughout: nothing here throws on a malformed pack, it
 * falls back instead.
 */
class PackPalette
{
    /**
     * Keys a pack's `color.properties` may use for a line/border color, most
     * representative of "the pack's accent" first. The first one that parses and
     * reads clearly against the sampled background wins; only 3 of the packs
     * ship the file at all, so this is a preference, never a requirement.
     *
     * @var list<string>
     */
    private const ACCENT_PROPERTY_KEYS = [
        'world_switch_top_and_bottom_lines',
        'bank_tab_underline',
        'chatbox_seperator_line',
        'account_management_tab_line',
        'quest_tab_tabs_underline',
    ];

    /** Fraction of the remaining lightness range a derived accent travels. */
    private const ACCENT_LIGHTNESS_SHIFT = 0.35;

    /** Mild lift so the accent reads as the pack's color, not a wash of it. */
    private const ACCENT_SATURATION_BOOST = 1.15;

    /** RGB chroma (0..1) below which a background counts as grey. */
    private const ACCENT_CHROMA_FLOOR = 0.05;

    /**
     * WCAG 2.1 SC 1.4.11 (non-text contrast): the ratio a boundary has to clear
     * against its background to be a dependable UI edge. An accent this class
     * derives is held to it by construction, and {@see uiAccent()} lifts any
     * accent that falls short of it.
     */
    private const ACCENT_TARGET_CONTRAST = 3.0;

    /**
     * Contrast a pack author's declared accent must reach for us to prefer it
     * over a derived one — one full step below {@see ACCENT_TARGET_CONTRAST}.
     *
     * Readability is not what this bar protects: functional boundaries use
     * {@see uiAccent()}, which clears the WCAG bar whatever the accent is. The
     * declared accent is decorative, so the only question it has to answer is
     * whether the colour still reads as a deliberate line rather than as a
     * rendering fault, and an edge stops reading as deliberate somewhere around
     * 2:1. Being an absolute floor rather than a margin below the derived
     * accent matters when the derivation overshoots (pack-rs3 reaches 4.81:1) or
     * is clamped: neither should change what counts as a visible line.
     */
    private const ACCENT_MIN_DECLARED_CONTRAST = 2.0;

    /**
     * sRGB relative luminance at which contrast against white and against black
     * are equal — the point where "is this background dark?" flips.
     */
    private const DARK_BACKGROUND_LUMINANCE = 0.179;

    /**
     * Sample a representative background color from the pack's PNG assets, then
     * pair it with an accent: the pack author's own declared line color when the
     * pack ships a usable one, otherwise one derived from the background. Tries a
     * handful of known-large-area assets in order; the first one that loads wins.
     *
     * @return array{background_color: ?string, accent_color: ?string}
     */
    public function extractPaletteColors(string $packDir): array
    {
        if (! extension_loaded('gd')) {
            return ['background_color' => null, 'accent_color' => null];
        }

        $candidates = [
            'dialog/background.png',
            'chatbox/background.png',
            'fixed_mode/side_panel_background.png',
        ];

        foreach ($candidates as $rel) {
            $path = $packDir.'/'.$rel;
            if (! is_file($path)) {
                continue;
            }

            $bg = $this->averageColor($path);
            if ($bg === null) {
                continue;
            }

            return [
                'background_color' => $bg,
                'accent_color' => $this->preferredAccent($packDir, $bg),
            ];
        }

        return ['background_color' => null, 'accent_color' => null];
    }

    /**
     * Average the RGB of a 3×3 grid of interior pixels, skipping near-transparent
     * ones. Returns a `#rrggbb` hex string, or null when nothing usable was found.
     */
    private function averageColor(string $path): ?string
    {
        $info = @getimagesize($path);
        if ($info === false) {
            return null;
        }

        $image = @imagecreatefrompng($path);
        if ($image === false) {
            return null;
        }

        try {
            [$width, $height] = $info;
            $samples = [];

            for ($i = 1; $i <= 3; $i++) {
                for ($j = 1; $j <= 3; $j++) {
                    $x = (int) ($width * $i / 4);
                    $y = (int) ($height * $j / 4);
                    $rgba = imagecolorat($image, $x, $y);

                    // GD's alpha runs 0 (opaque) .. 127 (fully transparent).
                    $alpha = ($rgba >> 24) & 0x7F;
                    if ($alpha > 64) {
                        continue;
                    }

                    $samples[] = [
                        ($rgba >> 16) & 0xFF,
                        ($rgba >> 8) & 0xFF,
                        $rgba & 0xFF,
                    ];
                }
            }

            if ($samples === []) {
                return null;
            }

            $count = count($samples);
            $r = (int) round(array_sum(array_column($samples, 0)) / $count);
            $g = (int) round(array_sum(array_column($samples, 1)) / $count);
            $b = (int) round(array_sum(array_column($samples, 2)) / $count);

            return sprintf('#%02x%02x%02x', $r, $g, $b);
        } finally {
            imagedestroy($image);
        }
    }

    /**
     * The accent to store for a pack: the author's own declared colour where the
     * pack ships a usable one, and otherwise one derived from the background.
     *
     * The bundled default pack is the exception, and deliberately not one the
     * contrast rules can express. It is what every user who has never picked a
     * pack sees, and its declared accent (`#807660`, 2.30:1) reads visibly
     * fainter than the one the derivation reaches (`#9e8a6f`, 3.11:1). No
     * threshold can single it out — its declared accent beats pack-crazed's on
     * contrast (2.30 vs 2.03) *and* sits closer to its own derived accent (0.81
     * vs 1.05 below) — so preferring authenticity everywhere except the default
     * interface is stated here as the policy it is.
     */
    private function preferredAccent(string $packDir, string $background): string
    {
        if (basename($packDir) !== InstallResourcePack::VANILLA_PACK) {
            $declared = $this->declaredAccent($packDir, $background);

            if ($declared !== null) {
                return $declared;
            }
        }

        return $this->deriveAccent($background);
    }

    /**
     * The pack author's own accent, read from the `color.properties` file packs
     * may ship in their root ({@see ACCENT_PROPERTY_KEYS}). Returns null when the
     * pack ships no such file, when nothing in it parses, or when every declared
     * candidate sits too close to the sampled background to read as a border —
     * the caller then falls back to {@see deriveAccent()}.
     *
     * Pack input is untrusted: anything unparseable is skipped, never thrown on.
     */
    private function declaredAccent(string $packDir, string $background): ?string
    {
        $path = $packDir.'/color.properties';
        if (! is_file($path)) {
            return null;
        }

        $contents = @file_get_contents($path);
        if ($contents === false) {
            return null;
        }

        $declared = [];
        foreach (preg_split('/\R/', $contents) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $declared[trim($key)] = trim($value);
        }

        foreach (self::ACCENT_PROPERTY_KEYS as $key) {
            if (! isset($declared[$key])) {
                continue;
            }

            $hex = self::parsePropertyColor($declared[$key]);
            if ($hex === null) {
                continue;
            }

            if (self::contrastRatio($hex, $background) < self::ACCENT_MIN_DECLARED_CONTRAST) {
                continue;
            }

            return $hex;
        }

        return null;
    }

    /**
     * Parse one `color.properties` value into `#rrggbb`, or null when it isn't a
     * usable color. Values are `0x`-prefixed hex, either RGB or ARGB (alpha
     * first, as the files' own header comment states), with leading zeros
     * stripped upstream — so `0x98a0` means `#0098a0` and `0x9c802bc4` means
     * `#802bc4`.
     */
    private static function parsePropertyColor(string $value): ?string
    {
        $digits = preg_replace('/^0x/i', '', trim($value)) ?? '';
        if (! preg_match('/^[0-9a-f]{1,8}$/i', $digits)) {
            return null;
        }

        $digits = strlen($digits) > 6
            ? substr(str_pad($digits, 8, '0', STR_PAD_LEFT), 2)
            : str_pad($digits, 6, '0', STR_PAD_LEFT);

        return '#'.strtolower($digits);
    }

    /**
     * Derive an accent from the background by moving its HSL lightness away from
     * it while keeping the hue (and mildly lifting the saturation), so a vivid
     * pack gets a vivid accent. Relative luminance picks the direction — dark
     * background → lighter accent, light background → darker one — and the
     * lightness is then pushed until the pair clears
     * {@see ACCENT_TARGET_CONTRAST}.
     */
    private function deriveAccent(string $hex): string
    {
        [$red, $green, $blue] = self::hexToRgb($hex);
        [$hue, $saturation, $lightness] = self::rgbToHsl($red, $green, $blue);

        // Hue and saturation are unreliable this close to the neutral axis:
        // #000009 is black to the eye but fully saturated blue in HSL, so
        // amplifying it would paint a near-black pack with vivid blue borders.
        $chroma = (max($red, $green, $blue) - min($red, $green, $blue)) / 255;
        $saturation = $chroma < self::ACCENT_CHROMA_FLOOR
            ? 0.0
            : min(1.0, $saturation * self::ACCENT_SATURATION_BOOST);

        $lighten = self::isDark($hex);
        $lightness = $lighten
            ? $lightness + self::ACCENT_LIGHTNESS_SHIFT * (1 - $lightness)
            : $lightness * (1 - self::ACCENT_LIGHTNESS_SHIFT);

        return self::shiftUntilContrast(
            $hue,
            $saturation,
            $lightness,
            $hex,
            self::ACCENT_TARGET_CONTRAST,
            $lighten,
        );
    }

    /**
     * The accent to paint functional controls with: the accent itself when it
     * already clears {@see ACCENT_TARGET_CONTRAST} against the background, and
     * otherwise the same hue moved away from the background until it does.
     *
     * A pack's declared accent is chosen to look right on the pack's own
     * artwork, not to carry a form control — ScrewedPurple's `#802bc4` is the
     * authentic border colour but only reaches 2.63:1 on its background. This
     * keeps that colour available for decorative edges while giving inputs and
     * other boundaries a version that meets WCAG 1.4.11.
     */
    public function uiAccent(string $accent, string $background): string
    {
        if (self::contrastRatio($accent, $background) >= self::ACCENT_TARGET_CONTRAST) {
            return $accent;
        }

        [$hue, $saturation, $lightness] = self::rgbToHsl(...self::hexToRgb($accent));

        return self::shiftUntilContrast(
            $hue,
            $saturation,
            $lightness,
            $background,
            self::ACCENT_TARGET_CONTRAST,
            self::isDark($background),
        );
    }

    /**
     * Walk an HSL colour's lightness away from `$against` — keeping its hue and
     * saturation — until the two reach `$target` contrast. Stops early once the
     * lightness clamps, which is as far apart as this hue can get.
     */
    private static function shiftUntilContrast(
        float $hue,
        float $saturation,
        float $lightness,
        string $against,
        float $target,
        bool $lighten,
    ): string {
        $color = self::hslToHex($hue, $saturation, $lightness);

        // A saturated hue carries far less relative luminance than the grey of
        // the same lightness, so a fixed lightness shift alone lands short.
        for ($step = 0; $step < 60; $step++) {
            if (self::contrastRatio($color, $against) >= $target) {
                break;
            }

            $next = $lighten ? min(0.97, $lightness + 0.02) : max(0.03, $lightness - 0.02);
            if (abs($next - $lightness) < 1e-9) {
                break; // Clamped — as far from the background as this hue goes.
            }

            $lightness = $next;
            $color = self::hslToHex($hue, $saturation, $lightness);
        }

        return $color;
    }

    /**
     * @return array{int, int, int}
     */
    private static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * WCAG 2.1 relative luminance (0..1). Each channel is linearized out of
     * sRGB's transfer curve *before* the Rec. 709 weights are applied —
     * weighting the raw channel values instead overstates how much light a
     * saturated colour actually carries, which is what makes a vivid purple
     * look far enough from its background when it is not.
     */
    private static function relativeLuminance(string $hex): float
    {
        [$r, $g, $b] = array_map(
            static fn (int $channel): float => ($c = $channel / 255) <= 0.03928
                ? $c / 12.92
                : (($c + 0.055) / 1.055) ** 2.4,
            self::hexToRgb($hex),
        );

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /**
     * WCAG 2.1 contrast ratio between two colors, 1.0 (identical) to 21.0
     * (black on white).
     */
    public static function contrastRatio(string $a, string $b): float
    {
        $first = self::relativeLuminance($a);
        $second = self::relativeLuminance($b);

        return (max($first, $second) + 0.05) / (min($first, $second) + 0.05);
    }

    /**
     * Whether a color is dark enough that an accent should be lighter than it
     * rather than darker — i.e. it contrasts better with white than with black.
     */
    private static function isDark(string $hex): bool
    {
        return self::relativeLuminance($hex) < self::DARK_BACKGROUND_LUMINANCE;
    }

    /**
     * @return array{float, float, float} hue in degrees, saturation and lightness in 0..1
     */
    private static function rgbToHsl(int $red, int $green, int $blue): array
    {
        $r = $red / 255;
        $g = $green / 255;
        $b = $blue / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $lightness = ($max + $min) / 2;
        $delta = $max - $min;

        if ($delta <= 0.0) {
            return [0.0, 0.0, $lightness];
        }

        $saturation = $lightness > 0.5
            ? $delta / (2.0 - $max - $min)
            : $delta / ($max + $min);

        $hue = match (true) {
            $max === $r => fmod((($g - $b) / $delta) + ($g < $b ? 6 : 0), 6),
            $max === $g => (($b - $r) / $delta) + 2,
            default => (($r - $g) / $delta) + 4,
        };

        return [$hue * 60, $saturation, $lightness];
    }

    /**
     * @param  float  $hue  in degrees
     * @param  float  $saturation  0..1
     * @param  float  $lightness  0..1
     */
    private static function hslToHex(float $hue, float $saturation, float $lightness): string
    {
        $lightness = max(0.0, min(1.0, $lightness));
        $chroma = (1 - abs(2 * $lightness - 1)) * $saturation;
        $sector = fmod(fmod($hue, 360) + 360, 360) / 60;
        $secondary = $chroma * (1 - abs(fmod($sector, 2) - 1));

        [$r, $g, $b] = match ((int) floor($sector)) {
            0 => [$chroma, $secondary, 0.0],
            1 => [$secondary, $chroma, 0.0],
            2 => [0.0, $chroma, $secondary],
            3 => [0.0, $secondary, $chroma],
            4 => [$secondary, 0.0, $chroma],
            default => [$chroma, 0.0, $secondary],
        };

        $offset = $lightness - $chroma / 2;

        return sprintf(
            '#%02x%02x%02x',
            (int) round(max(0.0, min(1.0, $r + $offset)) * 255),
            (int) round(max(0.0, min(1.0, $g + $offset)) * 255),
            (int) round(max(0.0, min(1.0, $b + $offset)) * 255),
        );
    }
}
