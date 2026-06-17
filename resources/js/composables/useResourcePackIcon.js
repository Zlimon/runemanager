import { usePage } from "@inertiajs/vue3";

/*
 * Resolve icons against the active resource pack, falling back to the bundled
 * /images set. Packs ship in-game sprites under /resource-packs/{name}/{kind}/,
 * but only for skills and UI chrome (tab/, bank/, skill/, stats/ …) — there are
 * no boss/ or clue/ dirs, so those stay on the local bundle.
 *
 * Pair packIcon()/skillIcon() (which return null when no pack is installed, and
 * a URL that may 404 when the pack predates an icon) with a bundled fallback via
 * the shared onIconError handler:
 *
 *   <img :src="skillIcon(slug) ?? `/images/skill/${slug}.png`"
 *        @error="onIconError($event, `/images/skill/${slug}.png`)">
 */
export function useResourcePackIcon() {
    const page = usePage();

    // The pack's copy of {kind}/{slug}.png, or null when no pack is active.
    // ?v={version} busts the browser cache when a pack is re-installed.
    const packIcon = (kind, slug) => {
        const pack = page.props.pack;
        return pack?.name
            ? `/resource-packs/${pack.name}/${kind}/${slug}.png?v=${pack.version ?? ''}`
            : null;
    };

    // Packs file skill icons under skill/, naming Overall "total" (the local
    // bundle calls it "overall"), so translate on the way out.
    const skillIcon = (slug) => packIcon("skill", slug === "overall" ? "total" : slug);

    // Swap a failed pack icon to its bundled fallback once, then hide if that
    // fails too (e.g. a slug with no local file at all).
    const onIconError = (event, fallback) => {
        const img = event.target;
        if (fallback && !img.src.endsWith(fallback)) {
            img.src = fallback;
            return;
        }
        img.onerror = null;
        img.style.display = "none";
    };

    return { packIcon, skillIcon, onIconError };
}
