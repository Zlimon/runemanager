<?php

namespace App\Http\Controllers;

use App\Models\ResourcePack;
use App\Services\ResourcePacks\InstallResourcePack;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Every pack-textured component on one page, with a switcher that repaints them
 * as any installed pack without changing what the viewer (or the instance) has
 * selected.
 *
 * It exists because pack regressions are invisible one component at a time: a
 * button's centre fill looked correct on Vanilla purely because that
 * pack's face and frame are a few RGB points apart, and only showed as a
 * mismatched panel on a pack whose frame is near-black. Seeing every component
 * against every pack at once is the only cheap way to catch that.
 */
class StyleguideController extends Controller
{
    /**
     * Pack sprite directories the page reports on. Kept to the ones per-pack.css
     * and the frontend actually address, so a gap here means a visibly broken
     * component rather than an unused file.
     *
     * @var list<string>
     */
    private const SPRITE_DIRS = ['button', 'dialog', 'options', 'scrollbar', 'equipment', 'skill'];

    public function index(): Response
    {
        return Inertia::render('Styleguide', [
            'packs' => $this->packs(),
        ]);
    }

    /**
     * The sprite inventory for one pack, as JSON.
     *
     * Deliberately not a deferred Inertia prop: loading it would then be an
     * Inertia visit, and every visit re-sends the shared props that
     * DarkModeToggle watches to restamp <html data-theme> — undoing the pack
     * the switcher is previewing. It is also only wanted on demand, so keeping
     * it off the page payload saves reading six directories per visit.
     */
    public function sprites(Request $request): JsonResponse
    {
        return response()->json(
            $this->inventory((string) $request->query('pack', '')),
        );
    }

    /**
     * Every installed pack, with what the switcher needs to repaint the page the
     * way app.blade.php would have server-side: the stylesheet URL (cache-busted
     * on the CSS mtime, as the Blade root does, so a template edit is picked up),
     * the palette variables, and whether it is the fixed-scheme vanilla pack.
     *
     * @return list<array{name: string, alias: string, css: ?string, background_color: ?string, accent_color: ?string, accent_ui_color: ?string, is_vanilla: bool, dark_mode: bool}>
     */
    private function packs(): array
    {
        return ResourcePack::query()
            ->orderBy('alias')
            ->get()
            ->map(fn (ResourcePack $pack): array => [
                'name' => $pack->name,
                'alias' => $pack->alias,
                'css' => $this->stylesheet($pack->name),
                'background_color' => $pack->background_color,
                'accent_color' => $pack->accent_color,
                // Derived on read from the two stored colours — the contrast-safe
                // accent for controls, which the switcher must repaint alongside
                // --pack-accent or a switched pack keeps the old one's edges.
                'accent_ui_color' => $pack->accent_ui_color,
                'is_vanilla' => $pack->isVanilla(),
                // The pack's own light/dark hint. Instance::resolveDarkMode()
                // treats it as the last fallback; the switcher uses it to open a
                // pack in the mode it was drawn for instead of stranding a dark
                // pack on the light theme.
                'dark_mode' => (bool) $pack->dark_mode,
            ])
            ->values()
            ->all();
    }

    /**
     * The pack's stylesheet URL, or null when the pack row exists but its files
     * don't — a state the admin packs screen already surfaces, and one the
     * switcher has to survive rather than render a dead <link> for.
     */
    private function stylesheet(string $name): ?string
    {
        $path = public_path("resource-packs/{$name}/resource-pack.css");

        if (! File::exists($path)) {
            return null;
        }

        return asset("resource-packs/{$name}/resource-pack.css").'?v='.filemtime($path);
    }

    /**
     * Which sprites the given pack ships itself and which are Vanilla's,
     * copied in at install time by {@see InstallResourcePack::completeFromVanilla()}.
     *
     * Packs only override what they change, so "the pack has this file" is not
     * the same as "the pack drew this file" — Knight's Fortress ships 16 button
     * sprites and inherits the rest. Comparing bytes against vanilla is what
     * separates the two, and it is the fact that explains why two packs can put
     * a different frame around an identical face.
     *
     * @return array{pack: ?string, own: int, borrowed: int, files: list<array{path: string, width: int, height: int, own: bool}>}
     */
    private function inventory(string $name): array
    {
        $empty = ['pack' => null, 'own' => 0, 'borrowed' => 0, 'files' => []];

        if ($name === '') {
            return $empty;
        }

        $packDir = public_path("resource-packs/{$name}");
        if (! File::isDirectory($packDir)) {
            return $empty;
        }

        $vanillaDir = public_path('resource-packs/'.InstallResourcePack::VANILLA_PACK);
        $isVanilla = $name === InstallResourcePack::VANILLA_PACK;

        $files = [];
        $own = 0;

        foreach (self::SPRITE_DIRS as $dir) {
            if (! File::isDirectory("{$packDir}/{$dir}")) {
                continue;
            }

            foreach (File::files("{$packDir}/{$dir}") as $file) {
                $relative = $dir.'/'.$file->getFilename();
                $vanillaCopy = "{$vanillaDir}/{$relative}";

                // Vanilla is the baseline, so it owns everything it ships; for any
                // other pack a byte-identical file is one it borrowed.
                $isOwn = $isVanilla || ! File::exists($vanillaCopy)
                    || File::get($file->getPathname()) !== File::get($vanillaCopy);

                $size = @getimagesize($file->getPathname());

                $files[] = [
                    'path' => $relative,
                    'width' => $size[0] ?? 0,
                    'height' => $size[1] ?? 0,
                    'own' => $isOwn,
                ];

                $own += $isOwn ? 1 : 0;
            }
        }

        usort($files, fn (array $a, array $b): int => strcmp($a['path'], $b['path']));

        return [
            'pack' => $name,
            'own' => $own,
            'borrowed' => count($files) - $own,
            'files' => $files,
        ];
    }
}
