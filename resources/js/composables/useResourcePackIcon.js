import { usePage } from "@inertiajs/vue3";

/*
 * Resolve icons against the active resource pack. Packs ship in-game sprites
 * under /resource-packs/{name}/{kind}/ for skills and UI chrome (tab/, bank/,
 * skill/, stats/ …) — there are no boss/ or clue/ dirs.
 *
 * Skills always have a source: the bundled Default Vanilla pack is the baseline
 * (a pack is always in effect, every pack borrows vanilla's sprites on install,
 * and admins can reinstall it), so a missing skill icon falls back to vanilla's
 * copy via vanillaSkillIcon() rather than a duplicated /images bundle:
 *
 *   <img :src="skillIcon(slug) ?? vanillaSkillIcon(slug)"
 *        @error="onIconError($event, vanillaSkillIcon(slug))">
 *
 * Icons with no pack equivalent (boss, clue, account-type, backgrounds) keep a
 * local /images fallback — pass that path to onIconError instead.
 */
export function useResourcePackIcon() {
    const page = usePage();

    // The bundled baseline pack, mirroring InstallResourcePack::VANILLA_PACK.
    const VANILLA = "sample-vanilla";

    // Packs file skill icons under skill/, naming Overall "total" (the local
    // bundle calls it "overall"), so translate on the way out.
    const skillSlug = (slug) => (slug === "overall" ? "total" : slug);

    // The active pack's copy of {kind}/{slug}.png, or null when no pack is in
    // effect. ?v={version} busts the browser cache when a pack is re-installed.
    const packIcon = (kind, slug) => {
        const pack = page.props.pack;
        return pack?.name
            ? `/resource-packs/${pack.name}/${kind}/${slug}.png?v=${pack.version ?? ''}`
            : null;
    };

    const skillIcon = (slug) => packIcon("skill", skillSlug(slug));

    // The bundled Default Vanilla pack's copy — the canonical skill-icon fallback.
    const vanillaSkillIcon = (slug) => `/resource-packs/${VANILLA}/skill/${skillSlug(slug)}.png`;

    // Swap a failed icon to its fallback once, then hide if that fails too.
    const onIconError = (event, fallback) => {
        const img = event.target;
        if (fallback && !img.src.endsWith(fallback)) {
            img.src = fallback;
            return;
        }
        img.onerror = null;
        img.style.display = "none";
    };

    return { packIcon, skillIcon, vanillaSkillIcon, onIconError };
}
