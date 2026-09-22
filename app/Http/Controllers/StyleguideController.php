<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ResourcePack;
use App\Services\ResourcePacks\PackSpriteInventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
     * Real catalogue items for the item slots, tooltips and loot grids: coins,
     * an abyssal whip, dragon bones, sharks, a twisted bow and a rune platebody —
     * stackable and unstackable, cheap and expensive, so every quantity format
     * the slot knows has a sample.
     *
     * @var list<int>
     */
    private const SAMPLE_ITEM_IDS = [995, 4151, 536, 385, 20997, 1127];

    public function index(): Response
    {
        return Inertia::render('Styleguide/Index', [
            'packs' => $this->packs(),
            'items' => array_values(Item::lookupByOsrsIds(self::SAMPLE_ITEM_IDS)),
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
    public function sprites(Request $request, PackSpriteInventory $inventory): JsonResponse
    {
        return response()->json(
            $inventory->for((string) $request->query('pack', '')),
        );
    }

    /**
     * Every installed pack in its picker shape ({@see ResourcePack::toPickerArray()}),
     * so the pack components can be shown with real icons and details, plus
     * what the switcher needs to repaint the page the way app.blade.php would
     * have server-side: the stylesheet URL (cache-busted on the CSS mtime, as the
     * Blade root does, so a template edit is picked up), the contrast-safe
     * accent, and whether it is the fixed-scheme vanilla pack. The picker
     * shape's dark_mode doubles as the switcher's hint for which mode to open a
     * pack in, the same last fallback Instance::resolveDarkMode() uses.
     *
     * @return list<array{id: int, name: string, alias: string, icon_url: ?string, dark_mode: bool, installed_by_user_id: ?int, author: ?string, version: ?string, tags: list<string>, background_color: ?string, accent_color: ?string, users_count: ?int, css: ?string, accent_ui_color: ?string, is_vanilla: bool}>
     */
    private function packs(): array
    {
        return ResourcePack::query()
            ->orderBy('alias')
            ->get()
            ->map(fn (ResourcePack $pack): array => [
                ...$pack->toPickerArray(),
                'css' => $pack->stylesheetUrl(),
                // Derived on read from the two stored colours — the contrast-safe
                // accent for controls, which the switcher must repaint alongside
                // --pack-accent or a switched pack keeps the old one's edges.
                'accent_ui_color' => $pack->accent_ui_color,
                'is_vanilla' => $pack->isVanilla(),
            ])
            ->values()
            ->all();
    }
}
