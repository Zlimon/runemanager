<?php

namespace App\Services\ResourcePacks;

use Illuminate\Support\Facades\File;

/**
 * Which sprites a pack ships itself and which are Vanilla's, copied in at
 * install time by {@see InstallResourcePack::completeFromVanilla()}.
 *
 * Packs only override what they change, so "the pack has this file" is not
 * the same as "the pack drew this file" — Knight's Fortress ships 16 button
 * sprites and inherits the rest. Comparing bytes against vanilla is what
 * separates the two, and it is the fact that explains why two packs can put
 * a different frame around an identical face.
 */
class PackSpriteInventory
{
    /**
     * Pack sprite directories reported on. Kept to the ones per-pack.css and
     * the frontend actually address, so a gap here means a visibly broken
     * component rather than an unused file.
     *
     * @var list<string>
     */
    public const SPRITE_DIRS = ['button', 'dialog', 'options', 'scrollbar', 'equipment', 'skill'];

    /**
     * @return array{pack: ?string, own: int, borrowed: int, files: list<array{path: string, width: int, height: int, own: bool}>}
     */
    public function for(string $name): array
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
