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
            $this->copyTemplateCss($extractTarget);
            $this->completeFromVanilla($name, $extractTarget);
            $this->normalizeBorderSprites($extractTarget);
        } finally {
            @unlink($zipPath);
        }

        $colors = $this->extractPaletteColors($extractTarget);

        return $this->upsertRow($name, $colors);
    }

    /**
     * Fill in any per-pack.css-referenced sprite this pack doesn't ship by copying
     * the vanilla default's version. Packs only override the sprites they change,
     * so without this a pack that omits (say) the orb sprites would render those
     * elements blank instead of falling back to the standard look.
     */
    private function completeFromVanilla(string $name, string $extractTarget): void
    {
        if ($name === self::VANILLA_PACK) {
            return;
        }

        $vanilla = public_path('resource-packs/'.self::VANILLA_PACK);
        if (! File::isDirectory($vanilla)) {
            return; // Default not installed yet — nothing to borrow from.
        }

        self::fillMissingAssets($extractTarget, $vanilla, $this->referencedAssets());
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
     * SPEC §6 — ensure the bundled "Default Vanilla" pack has a DB row so it's
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

        $colors = $this->extractPaletteColors($dir);

        $pack = new ResourcePack;
        $pack->name = self::VANILLA_PACK;
        $pack->alias = 'Default Vanilla';
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

    private function copyTemplateCss(string $extractTarget): void
    {
        $template = resource_path('css/per-pack.css');
        if (! File::exists($template)) {
            throw new RuntimeException('Per-pack CSS template missing at '.$template);
        }
        File::copy($template, $extractTarget.'/resource-pack.css');
    }

    /**
     * @param  array{background_color: ?string, accent_color: ?string}  $colors
     */
    private function upsertRow(string $name, array $colors): ResourcePack
    {
        $properties = $this->fetchProperties($name);
        $tags = $properties ? array_map('trim', explode(',', $properties['tags'] ?? '')) : [];
        $tags = array_map(fn ($t) => Str::lower($t), array_filter($tags));

        // ResourcePack has no $fillable — set attributes directly to avoid mass-assignment guards.
        $pack = ResourcePack::where('name', $name)->first() ?? new ResourcePack;
        $pack->name = $name;
        $pack->alias = $properties['displayName'] ?? Str::title(str_replace(['pack-', '-'], ' ', $name));
        $pack->version = $properties['compatibleVersion'] ?? '1.0.0';
        $pack->author = $properties['author'] ?? 'unknown';
        $pack->url = sprintf('https://github.com/melkypie/resource-packs/archive/%s.zip', $name);
        $pack->tags = implode(',', $tags);
        $pack->dark_mode = in_array('dark', $tags, true);
        $pack->background_color = $colors['background_color'];
        $pack->accent_color = $colors['accent_color'];
        $pack->save();

        return $pack;
    }

    /**
     * Sample a representative background color from the pack's PNG assets, then
     * derive an accent by shifting luminance away from it. Tries a handful of
     * known-large-area assets in order; the first one that loads wins.
     *
     * @return array{background_color: ?string, accent_color: ?string}
     */
    private function extractPaletteColors(string $packDir): array
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
                'accent_color' => $this->shiftAgainstLuminance($bg, 0.35),
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
     * Shift the given hex color toward white if it's dark, toward black if it's
     * light — perceived-luminance threshold (Rec. 709 coefficients) decides which
     * direction. `$amount` is a 0..1 fraction of the gap to traverse.
     */
    private function shiftAgainstLuminance(string $hex, float $amount): string
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $luminance = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;

        if ($luminance < 128) {
            // Dark background → lighter accent.
            $r += (255 - $r) * $amount;
            $g += (255 - $g) * $amount;
            $b += (255 - $b) * $amount;
        } else {
            // Light background → darker accent.
            $r *= (1 - $amount);
            $g *= (1 - $amount);
            $b *= (1 - $amount);
        }

        return sprintf(
            '#%02x%02x%02x',
            max(0, min(255, (int) round($r))),
            max(0, min(255, (int) round($g))),
            max(0, min(255, (int) round($b))),
        );
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
