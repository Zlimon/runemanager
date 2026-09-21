<?php

namespace App\Services\ResourcePacks;

use App\Models\ResourcePack;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

/**
 * Download + extract a RuneLite resource pack into its own per-pack directory and
 * register/refresh the matching {@see ResourcePack} row.
 *
 * The pack ZIPs come from the {@code melkypie/resource-packs} GitHub repo where
 * each pack is a branch and the archive URL is
 * {@code https://github.com/melkypie/resource-packs/archive/{name}.zip}.
 *
 * Disk layout (per pack):
 *   {system tmp}/rpz_*.zip                          ← downloaded archive (deleted after extract)
 *   public/resource-packs/{name}/                   ← extracted, served as static assets
 *   public/resource-packs/{name}/resource-pack.css  ← copied from resources/css/per-pack.css
 *
 * The ZIP and the extraction scratch space live in the system temp dir rather
 * than under storage/, so a stale-perms staging directory can't permanently
 * block installs — temp is always writable by the running user.
 */
class InstallResourcePack
{
    /**
     * The complete baseline pack (the instance default). Any sprite a custom pack
     * doesn't ship falls back to this one's version (see {@see completeFromVanilla}),
     * so a partial pack never leaves a textured element blank.
     */
    public const VANILLA_PACK = 'sample-vanilla';

    /**
     * Canonical pixel size for each textured border edge sprite. Packs ship these
     * at one of two thicknesses (e.g. 36px vs 21px) — the thinner ones are the
     * thick sprite cropped on the inner side, which shifts where the border line
     * sits relative to the sprite's right/bottom edge and breaks the fixed CSS
     * offsets in per-pack.css. Padding every sprite back to the canonical size
     * (content anchored top-left, transparent fill) restores a uniform geometry.
     *
     * @var array<string, array{int, int}>
     */
    private const BORDER_SPRITE_SIZES = [
        'dialog/iron_rivets_edge_top.png' => [36, 36],
        'dialog/iron_rivets_vertical.png' => [36, 36],
        'dialog/iron_rivets_edge_right.png' => [36, 36],
        'dialog/iron_rivets_bottom.png' => [36, 36],
        'dialog/bottom_line_mode_side_panel_edge_top.png' => [32, 32],
        'dialog/bottom_line_mode_side_panel_edge_left.png' => [32, 32],
        'dialog/bottom_line_mode_side_panel_edge_right.png' => [32, 32],
        'dialog/bottom_line_mode_side_panel_edge_bottom.png' => [32, 32],
    ];

    /**
     * Pack-image directories the frontend addresses by pack name directly (not
     * via the per-pack CSS), so {@see referencedAssets()} never sees them. Every
     * file vanilla ships under these is borrowed when the pack omits it — e.g. a
     * pack missing equipment/slot_ammunition.png falls back to vanilla's instead
     * of rendering a broken image. Extend this when a new pack-sourced image
     * category is added to the frontend.
     *
     * @var list<string>
     */
    private const VANILLA_IMAGE_DIRS = ['equipment', 'skill'];

    public function __construct(private PackPalette $palette = new PackPalette) {}

    /**
     * @return ResourcePack the upserted row
     *
     * @throws RuntimeException on download / extract failure
     */
    public function install(string $name): ResourcePack
    {
        File::ensureDirectoryExists(public_path('resource-packs'));

        $zipPath = tempnam(sys_get_temp_dir(), 'rpz_');
        if ($zipPath === false) {
            throw new RuntimeException('Could not allocate temp file for pack download');
        }
        $extractTarget = public_path("resource-packs/{$name}");

        try {
            $this->download($name, $zipPath);
            $this->extract($zipPath, $extractTarget);
            $this->completeFromVanilla($extractTarget);
            // After completeFromVanilla, not before: rendering the template
            // inspects the pack's button/middle.png, which a partial pack only
            // has once it has borrowed vanilla's.
            $this->copyTemplateCss($extractTarget);
            $this->normalizeBorderSprites($extractTarget);
            $this->normalizeStatTileSprites($extractTarget);
            $this->normalizeCheckboxSprites($extractTarget);
            $this->generateHorizontalScrollbar($extractTarget);
        } finally {
            @unlink($zipPath);
        }

        $colors = $this->palette->extractPaletteColors($extractTarget);

        return $this->upsertRow($name, $colors);
    }

    /**
     * Fill in any sprite this pack doesn't ship by copying the vanilla default's
     * version — both the per-pack.css referenced sprites and the frontend-
     * addressed image dirs ({@see VANILLA_IMAGE_DIRS}). Packs only override what
     * they change, so without this a pack that omits (say) the orb sprites or an
     * equipment slot would render those elements blank/broken instead of falling
     * back to the standard look. No-op for the vanilla pack itself. Returns the
     * number of files copied (also used to backfill already-installed packs).
     */
    public function completeFromVanilla(string $packDir): int
    {
        if (basename($packDir) === self::VANILLA_PACK) {
            return 0;
        }

        $vanilla = public_path('resource-packs/'.self::VANILLA_PACK);
        if (! File::isDirectory($vanilla) || realpath($vanilla) === realpath($packDir)) {
            return 0; // Default not installed yet — nothing to borrow from.
        }

        $assets = array_merge($this->referencedAssets(), self::vanillaImageDirAssets($vanilla));

        return self::fillMissingAssets($packDir, $vanilla, $assets);
    }

    /**
     * Relative paths of every file vanilla ships under the frontend-addressed
     * image dirs, so a pack can borrow the ones it doesn't ship.
     *
     * @return list<string>
     */
    private static function vanillaImageDirAssets(string $vanillaDir): array
    {
        $assets = [];

        foreach (self::VANILLA_IMAGE_DIRS as $dir) {
            $path = $vanillaDir.'/'.$dir;
            if (! File::isDirectory($path)) {
                continue;
            }
            foreach (File::files($path) as $file) {
                $assets[] = $dir.'/'.$file->getFilename();
            }
        }

        return $assets;
    }

    /**
     * Pad every border edge sprite this pack ships up to its canonical size so the
     * fixed CSS border offsets land the border line on the panel edge regardless
     * of the thickness the pack originally shipped. Idempotent — sprites already
     * at (or above) the canonical size are left untouched.
     */
    public function normalizeBorderSprites(string $packDir): void
    {
        foreach (self::BORDER_SPRITE_SIZES as $relative => [$width, $height]) {
            $path = $packDir.'/'.$relative;
            if (File::exists($path)) {
                self::padPngTopLeft($path, $width, $height);
            }
        }
    }

    /**
     * The two halves of a skill stat tile, which {@see normalizeStatTileSprites}
     * trims to their opaque width.
     *
     * @var list<string>
     */
    private const STAT_TILE_SPRITES = [
        'stats/tile_half_left.png',
        'stats/tile_half_right.png',
        // The hover pair has to be cropped on the same rule as the resting pair:
        // .resource-pack-box:hover swaps only the images, keeping the 50% sizing,
        // so a padded _selected sprite reopens the very gap in the middle that
        // cropping the resting pair closed.
        'stats/tile_half_left_selected.png',
        'stats/tile_half_right_selected.png',
    ];

    /**
     * Trim the transparent margins from each stat-tile half so the pair meets
     * seam-to-seam when the CSS paints them at 50% of the cell each.
     *
     * Every pack draws roughly 31px of art on a 35 or 36px canvas, but pads it
     * differently — one pack's left half starts two pixels in, another's four.
     * Painted at a fixed width and pinned to opposite edges, those margins meet
     * in the middle as a transparent band and the card shows through the tile.
     * Cropping to the opaque box here means the CSS can size each half at 50%
     * and have the art actually touch, whatever the pack shipped.
     *
     * Horizontal only: the halves are already full-bleed vertically, and the
     * CSS stretches their height to the cell.
     */
    public function normalizeStatTileSprites(string $packDir): void
    {
        foreach (self::STAT_TILE_SPRITES as $relative) {
            $path = $packDir.'/'.$relative;
            if (File::exists($path)) {
                self::cropPngToOpaqueBox($path);
            }
        }
    }

    /**
     * The checkbox faces, which {@see normalizeCheckboxSprites} trims to their
     * drawn area.
     *
     * @var list<string>
     */
    private const CHECKBOX_SPRITES = [
        'options/checkbox_empty.png',
        'options/checkbox_empty_round.png',
        'options/checkbox_checked_red.png',
    ];

    /**
     * Trim each checkbox face to the pixels it actually draws.
     *
     * Packs ship these as a 36x36 canvas with the art tucked into the top-left
     * corner and the rest transparent, the glyph itself measuring somewhere
     * around 14-17px. The CSS paints them with `contain`, which fits that whole
     * canvas into the control, so the box and its tick rendered at under half
     * size and sat up and to the left of centre. Cropping to the drawn area lets
     * `contain` scale the art itself, which both centres it and fills the
     * control.
     *
     * Both axes here, unlike the stat tiles: a checkbox is a free-standing glyph
     * rather than one half of a seam, so its vertical padding is as unwanted as
     * its horizontal.
     */
    public function normalizeCheckboxSprites(string $packDir): void
    {
        foreach (self::CHECKBOX_SPRITES as $relative) {
            $path = $packDir.'/'.$relative;
            if (File::exists($path)) {
                self::cropPngToOpaqueBox($path, bothAxes: true);
            }
        }
    }

    /**
     * Crop a PNG to the span of its non-transparent pixels. Horizontal only by
     * default, keeping full height; pass {@code $bothAxes} to trim top and
     * bottom too. Idempotent: an image already flush with its art has nothing to
     * trim. No-op for a fully transparent or unreadable image.
     */
    private static function cropPngToOpaqueBox(string $path, bool $bothAxes = false): void
    {
        $src = @imagecreatefrompng($path);
        if ($src === false) {
            return;
        }

        try {
            $width = imagesx($src);
            $height = imagesy($src);
            $left = $width;
            $right = -1;
            $top = $height;
            $bottom = -1;

            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    // GD alpha runs 0 (opaque) .. 127 (fully transparent).
                    if (((imagecolorat($src, $x, $y) >> 24) & 0x7F) < 127) {
                        $left = min($left, $x);
                        $right = max($right, $x);
                        $top = min($top, $y);
                        $bottom = max($bottom, $y);
                    }
                }
            }

            if ($right < $left) {
                return;
            }

            if (! $bothAxes) {
                $top = 0;
                $bottom = $height - 1;
            }

            if ($left === 0 && $right === $width - 1 && $top === 0 && $bottom === $height - 1) {
                return;
            }

            $cropped = imagecreatetruecolor($right - $left + 1, $bottom - $top + 1);
            imagealphablending($cropped, false);
            imagesavealpha($cropped, true);
            imagecopy($cropped, $src, 0, 0, $left, $top, $right - $left + 1, $bottom - $top + 1);
            imagepng($cropped, $path);
            imagedestroy($cropped);
        } finally {
            imagedestroy($src);
        }
    }

    /**
     * Grow a PNG to at least {@code $targetW}×{@code $targetH}, keeping the existing
     * pixels anchored at the top-left and filling the new right/bottom area with
     * transparency. No-op when the image already meets both dimensions.
     */
    private static function padPngTopLeft(string $path, int $targetW, int $targetH): void
    {
        $size = getimagesize($path);
        if ($size === false) {
            return;
        }

        [$srcW, $srcH] = $size;
        if ($srcW >= $targetW && $srcH >= $targetH) {
            return;
        }

        $src = @imagecreatefrompng($path);
        if ($src === false) {
            return;
        }

        $dst = imagecreatetruecolor(max($srcW, $targetW), max($srcH, $targetH));
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagefilledrectangle($dst, 0, 0, imagesx($dst) - 1, imagesy($dst) - 1, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagecopy($dst, $src, 0, 0, 0, 0, $srcW, $srcH);
        imagepng($dst, $path);

        imagedestroy($src);
        imagedestroy($dst);
    }

    /**
     * The horizontal scrollbar sprites are just the vertical ones rotated 90°, but
     * packs rarely ship them — so they'd fall back to vanilla and clash with the
     * pack's own (vertical) scrollbar. Derive them from whatever vertical sprite
     * is in effect (the pack's own, or vanilla after completeFromVanilla) so the
     * two axes always match. Maps vertical source => horizontal target.
     *
     * @var array<string, string>
     */
    private const HORIZONTAL_SCROLLBAR = [
        'scrollbar/thumb_middle_dark.png' => 'scrollbar/horizontal_thumb_middle_dark.png',
        'scrollbar/thumb_middle.png' => 'scrollbar/horizontal_thumb_middle.png',
        'scrollbar/thumb_top.png' => 'scrollbar/horizontal_thumb_right.png',
        'scrollbar/thumb_bottom.png' => 'scrollbar/horizontal_thumb_left.png',
        // Arrow buttons: rotating clockwise, up points right and down points left.
        'scrollbar/arrow_up.png' => 'scrollbar/arrow_right.png',
        'scrollbar/arrow_down.png' => 'scrollbar/arrow_left.png',
    ];

    /**
     * Regenerate the horizontal scrollbar sprites by rotating the pack's vertical
     * ones 90° clockwise, so the horizontal bar always matches the vertical one.
     * No-op for the bundled vanilla pack (it ships correct horizontal sprites).
     */
    public function generateHorizontalScrollbar(string $packDir): void
    {
        if (basename($packDir) === self::VANILLA_PACK) {
            return;
        }

        foreach (self::HORIZONTAL_SCROLLBAR as $vertical => $horizontal) {
            $source = $packDir.'/'.$vertical;
            if (File::exists($source)) {
                self::rotatePngClockwise($source, $packDir.'/'.$horizontal);
            }
        }
    }

    /**
     * Write a 90°-clockwise rotation of a PNG to $dest, preserving transparency.
     */
    private static function rotatePngClockwise(string $source, string $dest): void
    {
        $img = @imagecreatefrompng($source);
        if ($img === false) {
            return;
        }

        imagealphablending($img, false);
        imagesavealpha($img, true);
        // imagerotate turns anticlockwise, so -90 gives clockwise.
        $rotated = imagerotate($img, -90, imagecolorallocatealpha($img, 0, 0, 0, 127));
        imagealphablending($rotated, false);
        imagesavealpha($rotated, true);
        imagepng($rotated, $dest);

        imagedestroy($img);
        imagedestroy($rotated);
    }

    /**
     * Copy every $relativeAssets file the pack is missing from the vanilla dir
     * (never overwriting one the pack ships). Returns how many were filled in.
     *
     * @param  array<int, string>  $relativeAssets
     */
    public static function fillMissingAssets(string $packDir, string $vanillaDir, array $relativeAssets): int
    {
        $copied = 0;

        foreach ($relativeAssets as $relative) {
            $dest = $packDir.'/'.$relative;
            $src = $vanillaDir.'/'.$relative;

            if (File::exists($dest) || ! File::exists($src)) {
                continue;
            }

            File::ensureDirectoryExists(dirname($dest));
            File::copy($src, $dest);
            $copied++;
        }

        return $copied;
    }

    /**
     * The relative sprite paths the per-pack CSS template loads via url(...).
     *
     * @return array<int, string>
     */
    public function referencedAssets(): array
    {
        $css = File::get(resource_path('css/per-pack.css'));
        preg_match_all("/url\\('([^']+\\.png)'\\)/", $css, $matches);

        return array_values(array_unique($matches[1]));
    }

    public function isInstalled(string $name): bool
    {
        return File::exists(public_path("resource-packs/{$name}/resource-pack.css"));
    }

    /**
     * SPEC §6 — ensure the bundled "Vanilla" pack has a DB row so it's
     * always selectable alongside "Default" (no pack). The assets ship with the
     * app, so this builds the row from disk (palette only) with no download.
     * Idempotent and cheap after the first call.
     */
    public function ensureVanilla(): ?ResourcePack
    {
        $existing = ResourcePack::where('name', self::VANILLA_PACK)->first();
        if ($existing) {
            return $existing;
        }

        $dir = public_path('resource-packs/'.self::VANILLA_PACK);
        if (! File::isDirectory($dir)) {
            return null;
        }

        $colors = $this->palette->extractPaletteColors($dir);

        $pack = new ResourcePack;
        $pack->name = self::VANILLA_PACK;
        $pack->alias = 'Vanilla';
        $pack->version = 'bundled';
        $pack->author = 'RuneManager';
        $pack->url = '';
        $pack->tags = '';
        $pack->dark_mode = false;
        $pack->background_color = $colors['background_color'];
        $pack->accent_color = $colors['accent_color'];
        $pack->save();

        return $pack;
    }

    /**
     * Fetch the {@code compatibleVersion} from the pack's upstream {@code pack.properties}.
     * Returns null if upstream is unreachable or the properties file is missing — callers
     * should treat that as "no information; don't change anything".
     */
    public function latestUpstreamVersion(string $name): ?string
    {
        $properties = $this->fetchProperties($name);

        return $properties['compatibleVersion'] ?? null;
    }

    private function download(string $name, string $zipPath): void
    {
        $url = sprintf('https://github.com/melkypie/resource-packs/archive/%s.zip', $name);

        $response = Http::timeout(60)
            ->withOptions(['allow_redirects' => true])
            ->get($url);

        if (! $response->ok()) {
            throw new RuntimeException(sprintf(
                'Failed to fetch resource pack "%s" from %s — HTTP %d',
                $name, $url, $response->status(),
            ));
        }

        File::put($zipPath, $response->body());
    }

    private function extract(string $zipPath, string $extractTarget): void
    {
        // Clean any prior extract for this pack so leftover files from a previous
        // version don't linger.
        if (File::exists($extractTarget)) {
            File::deleteDirectory($extractTarget);
        }
        File::ensureDirectoryExists($extractTarget);

        $tmpDir = sys_get_temp_dir().'/rpx_'.Str::random(8);
        File::ensureDirectoryExists($tmpDir);

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            File::deleteDirectory($tmpDir);
            throw new RuntimeException(sprintf('Could not open %s', $zipPath));
        }

        try {
            if (! $zip->extractTo($tmpDir)) {
                throw new RuntimeException(sprintf('Could not extract %s to %s', $zipPath, $tmpDir));
            }
        } finally {
            $zip->close();
        }

        // GitHub archive ZIPs nest everything under a single top-level dir
        // ({repo}-{branch}/). Move that dir's contents up one level.
        $entries = array_values(array_diff(scandir($tmpDir), ['.', '..']));
        if (count($entries) === 1 && is_dir($tmpDir.'/'.$entries[0])) {
            $inner = $tmpDir.'/'.$entries[0];
            File::copyDirectory($inner, $extractTarget);
        } else {
            File::copyDirectory($tmpDir, $extractTarget);
        }

        File::deleteDirectory($tmpDir);
    }

    /**
     * Re-copy the per-pack CSS template into every already-installed pack.
     *
     * {@see copyTemplateCss()} only runs at install time, so editing
     * resources/css/per-pack.css leaves every installed pack on the copy it was
     * given when it was fetched. That is true in production too: the deploy
     * shares public/resource-packs via symlink across releases, so a release
     * that changes the template ships CSS that never reaches a single pack.
     *
     * Packs whose copy already matches are left alone — app.blade.php cache-busts
     * the <link> on the file's mtime, so rewriting an identical file would expire
     * every viewer's cached stylesheet for nothing.
     *
     * Every installed pack is also re-completed from vanilla, because a template
     * edit can start referencing a sprite the pack never shipped (and only
     * borrowed at install time, from the older template's reference list). Without
     * this the new url() resolves to a 404 and that background layer renders as a
     * hole instead of falling back to the standard look.
     *
     * @return array{updated: list<string>, unchanged: list<string>, filled: int}
     */
    public function syncTemplateCss(): array
    {
        $template = resource_path('css/per-pack.css');
        if (! File::exists($template)) {
            throw new RuntimeException('Per-pack CSS template missing at '.$template);
        }

        $updated = [];
        $unchanged = [];
        $filled = 0;

        foreach (File::directories(public_path('resource-packs')) as $directory) {
            $target = $directory.'/resource-pack.css';

            // A directory without the stylesheet was never fully installed;
            // leave it to the installer rather than half-provisioning it here.
            if (! File::exists($target)) {
                continue;
            }

            $name = basename($directory);
            $filled += $this->completeFromVanilla($directory);

            // Re-run the sprite normalisers here, not just at install. A pack
            // fetched before a normaliser existed keeps whatever geometry it
            // shipped with for good, and in production public/resource-packs is
            // shared by symlink across releases, so installed packs outlive the
            // deploy that would have fixed them. All three are idempotent and
            // no-op on a pack that is already trimmed, so this is safe on every
            // run — the same reason completeFromVanilla() sits here.
            $this->normalizeBorderSprites($directory);
            $this->normalizeStatTileSprites($directory);
            $this->normalizeCheckboxSprites($directory);

            // Rendered per pack — see renderTemplateFor(). Comparing against the
            // raw template would rewrite every pack whose face is stretched on
            // every run, expiring cached stylesheets for no change.
            $contents = $this->renderTemplateFor($directory);

            if (File::get($target) === $contents) {
                $unchanged[] = $name;

                continue;
            }

            File::put($target, $contents);
            $updated[] = $name;
        }

        sort($updated);
        sort($unchanged);

        return ['updated' => $updated, 'unchanged' => $unchanged, 'filled' => $filled];
    }

    private function copyTemplateCss(string $extractTarget): void
    {
        File::put($extractTarget.'/resource-pack.css', $this->renderTemplateFor($extractTarget));
    }

    /**
     * A button face wider or taller than this is treated as a whole pre-rendered
     * face rather than a seamless swatch. The two kinds are far apart in
     * practice — small tiling swatches run around 12×12, pre-shaded faces
     * upwards of 56×56 — so nothing sits near the line, and a pack whose face
     * is a flat colour renders the same under either treatment.
     */
    private const TILEABLE_FACE_MAX = 24;

    /**
     * The per-pack CSS template with its :root block resolved for this pack.
     *
     * Only button/middle.png's treatment varies. Packs ship two different kinds
     * of art under that name: a small seamless swatch meant to tile, and a large
     * pre-shaded face meant to be drawn once. Tiling the second kind lays hard
     * vertical seams across a wide button, which is what reads as a coloured
     * panel inside the frame. Since each pack gets its own copy of the
     * stylesheet anyway, the choice is baked in here rather than asking CSS to
     * branch on something it cannot measure.
     *
     * @throws RuntimeException when the template is missing
     */
    private function renderTemplateFor(string $packDir): string
    {
        $template = resource_path('css/per-pack.css');
        if (! File::exists($template)) {
            throw new RuntimeException('Per-pack CSS template missing at '.$template);
        }

        $contents = File::get($template);
        $face = $packDir.'/button/middle.png';

        if (! File::exists($face)) {
            return $contents;
        }

        $size = @getimagesize($face);
        if ($size === false) {
            return $contents;
        }

        [$width, $height] = $size;
        if ($width <= self::TILEABLE_FACE_MAX && $height <= self::TILEABLE_FACE_MAX) {
            return $contents;
        }

        return str_replace(
            ['--pack-face-repeat: repeat;', '--pack-face-size: auto;'],
            ['--pack-face-repeat: no-repeat;', '--pack-face-size: 100% 100%;'],
            $contents,
        );
    }

    /**
     * @param  array{background_color: ?string, accent_color: ?string}  $colors
     */
    private function upsertRow(string $name, array $colors): ResourcePack
    {
        // ResourcePack has no $fillable — set attributes directly to avoid mass-assignment guards.
        $pack = ResourcePack::where('name', $name)->first() ?? new ResourcePack;
        $pack->name = $name;

        if ($name === self::VANILLA_PACK) {
            // Reinstalling the bundled default keeps the identity ensureVanilla()
            // gives it, regardless of what the upstream branch declares.
            $pack->alias = 'Vanilla';
            $pack->version = 'bundled';
            $pack->author = 'RuneManager';
            $pack->url = '';
            $pack->tags = '';
            $pack->dark_mode = false;
        } else {
            $properties = $this->fetchProperties($name);
            $tags = $properties ? array_map('trim', explode(',', $properties['tags'] ?? '')) : [];
            $tags = array_map(fn ($t) => Str::lower($t), array_filter($tags));

            $pack->alias = $properties['displayName'] ?? Str::title(str_replace(['pack-', '-'], ' ', $name));
            $pack->version = $properties['compatibleVersion'] ?? '1.0.0';
            $pack->author = $properties['author'] ?? 'unknown';
            $pack->url = sprintf('https://github.com/melkypie/resource-packs/archive/%s.zip', $name);
            $pack->tags = implode(',', $tags);
            // The sampled artwork decides, not the tag string: a pack author's
            // "dark" tag is optional and frequently absent from packs whose art
            // is plainly dark, and the flag picks which theme — and so which
            // text colour — is painted over that art. Falls back to the tag only
            // when no palette could be sampled at all.
            $pack->dark_mode = $colors['background_color'] !== null
                ? PackPalette::isDark($colors['background_color'])
                : in_array('dark', $tags, true);
        }

        $pack->background_color = $colors['background_color'];
        $pack->accent_color = $colors['accent_color'];
        $pack->save();

        return $pack;
    }

    /**
     * @return array<string, string>|null
     */
    private function fetchProperties(string $name): ?array
    {
        $url = sprintf('https://raw.githubusercontent.com/melkypie/resource-packs/%s/pack.properties', $name);
        $response = Http::timeout(20)->get($url);
        if (! $response->ok()) {
            return null;
        }

        $values = [];
        foreach (preg_split('/\r\n|\n|\r/', trim($response->body())) as $line) {
            if (! str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $values[trim($key)] = trim($value);
        }

        return $values ?: null;
    }
}
