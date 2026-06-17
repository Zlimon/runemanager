import { usePage } from "@inertiajs/vue3";

/*
 * One icon resolver: use the active resource pack's sprite, falling back to the
 * bundled Default Vanilla pack when the active pack doesn't ship it. Returns
 * <img> attributes to spread, so every pack icon is just:
 *
 *   <img v-bind="packIcon('skill', slug)" class="h-5 w-5">
 *
 * Icons that live in no pack (boss/clue hiscore icons, account-type badges) are
 * addressed straight from /images — pass `{ src: '/images/…' }` instead.
 */
export function useResourcePackIcon() {
    const page = usePage();

    // The bundled baseline pack, mirroring InstallResourcePack::VANILLA_PACK.
    const VANILLA = "sample-vanilla";

    const file = (name, kind, slug, version) =>
        `/resource-packs/${name}/${kind}/${slug}.png${version ? `?v=${version}` : ""}`;

    const packIcon = (kind, slug) => {
        // Packs name the Overall skill icon "total".
        if (kind === "skill" && slug === "overall") {
            slug = "total";
        }

        const pack = page.props.pack;
        const name = pack?.name ?? VANILLA;

        const fallback = file(VANILLA, kind, slug);

        return {
            src: file(name, kind, slug, pack?.version),
            // Swap to the vanilla copy if the active pack omits this sprite. The
            // src guard makes it idempotent — if vanilla is missing too it stops
            // rather than looping (those gaps are handled separately).
            onError:
                name === VANILLA
                    ? undefined
                    : (event) => {
                          if (!event.target.src.endsWith(fallback)) {
                              event.target.src = fallback;
                          }
                      },
        };
    };

    return { packIcon };
}
