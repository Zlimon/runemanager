import { unref } from "vue";
import { usePage } from "@inertiajs/vue3";

/*
 * One icon resolver: use the active resource pack's sprite, falling back to the
 * bundled Vanilla pack when the active pack doesn't ship it. Returns
 * <img> attributes to spread, so every pack icon is just:
 *
 *   <img v-bind="packIcon('skill', slug)" class="h-5 w-5">
 *
 * Icons that live in no pack (boss/clue hiscore icons, account-type badges) are
 * addressed straight from /images — pass `{ src: '/images/…' }` instead.
 *
 * `override` is for surfaces that preview a pack other than the active one (the
 * styleguide's switcher): pass a ref holding { name, version }, or holding null
 * to mean "no pack", and icons resolve against that instead of page.props.pack.
 * Omit it everywhere else — the shared prop is the source of truth.
 */
export function useResourcePackIcon(override = null) {
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

        // `override` present but holding null is a deliberate "no pack", which
        // still resolves to vanilla sprites — the same as the shared prop being
        // null. Only the absence of an override defers to the page.
        const pack = override ? unref(override) : page.props.pack;
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
