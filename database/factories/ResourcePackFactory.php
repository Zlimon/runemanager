<?php

namespace Database\Factories;

use App\Models\ResourcePack;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * An installed resource pack *row*.
 *
 * Pack files (sprites, `resource-pack.css`, `icon.png`) are gitignored and
 * downloaded at runtime, so a factory-made pack has a row and no files. That is
 * a state the app already handles — `icon_url` and `stylesheetUrl()` both return
 * null when the files are absent and every reader renders conditionally — so
 * the picker, Admin → Packs and Themes all have content, just without textures.
 * Run `resourcepack:ensure-vanilla` to give Vanilla its files for real.
 *
 * `ResourcePack` has no `HasFactory` trait, so build it as
 * `ResourcePackFactory::new()`.
 *
 * @extends Factory<ResourcePack>
 */
class ResourcePackFactory extends Factory
{
    protected $model = ResourcePack::class;

    /**
     * Demo pack identities, deliberately named after in-game interface eras
     * rather than after real hub packs, so nothing suggests an upstream pack is
     * installed when only a row exists.
     *
     * @var list<array{name: string, alias: string, author: string, tags: string, dark_mode: bool, background_color: string, accent_color: string}>
     */
    private const DEMO_PACKS = [
        [
            'name' => 'pack-demo-classic',
            'alias' => 'Classic Gielinor',
            'author' => 'RuneManager Demo',
            'tags' => 'classic,wood',
            'dark_mode' => false,
            'background_color' => '#d8ccb4',
            'accent_color' => '#8f7a44',
        ],
        [
            'name' => 'pack-demo-midnight',
            'alias' => 'Midnight',
            'author' => 'RuneManager Demo',
            'tags' => 'dark,minimal',
            'dark_mode' => true,
            'background_color' => '#1b1a24',
            'accent_color' => '#6f5ad6',
        ],
        [
            'name' => 'pack-demo-varrock',
            'alias' => 'Varrock Stone',
            'author' => 'RuneManager Demo',
            'tags' => 'stone,grey',
            'dark_mode' => true,
            'background_color' => '#2c2c30',
            'accent_color' => '#b0742c',
        ],
    ];

    /**
     * Hands out a different identity per call, suffixing once the list is
     * exhausted. `resource_packs.name` has no unique index, but two rows with
     * the same name would make the installer's `where('name', …)` ambiguous and
     * show the picker the same pack twice.
     */
    private static int $taken = 0;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $index = self::$taken++;
        $identity = self::DEMO_PACKS[$index % count(self::DEMO_PACKS)];
        $round = intdiv($index, count(self::DEMO_PACKS));

        $name = $round === 0 ? $identity['name'] : "{$identity['name']}-{$round}";

        return [
            ...$identity,
            'name' => $name,
            'version' => '1.0.0',
            // The shape InstallResourcePack builds for a hub pack; the demo
            // rows point at their own name so an update attempt fails visibly
            // rather than silently installing someone else's pack.
            'url' => sprintf('https://github.com/melkypie/resource-packs/archive/%s.zip', $name),
            'installed_by_user_id' => null,
        ];
    }

    /**
     * The bundled baseline, with the identity `InstallResourcePack::upsertRow()`
     * pins for it. It is the icon fallback for every viewer, so a demo instance
     * wants this row even if it wants no others.
     */
    public function vanilla(): static
    {
        return $this->state([
            'name' => ResourcePack::VANILLA,
            'alias' => 'Vanilla',
            'version' => 'bundled',
            'author' => 'RuneManager',
            'url' => '',
            'tags' => '',
            'dark_mode' => false,
            'background_color' => null,
            'accent_color' => null,
        ]);
    }
}
