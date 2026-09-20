<script setup>
import { computed, onBeforeUnmount, ref, watch } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Badge from "@/Components/Badge.vue";
import Card from "@/Components/Card.vue";
import Checkbox from "@/Components/Checkbox.vue";
import DangerButton from "@/Components/DangerButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import SelectInput from "@/Components/SelectInput.vue";
import TabbedCard from "@/Components/TabbedCard.vue";
import TextInput from "@/Components/TextInput.vue";
import { useResourcePackIcon } from "@/composables/useResourcePackIcon";

/*
 * Every pack-textured component in one place, repainted as any installed pack
 * without persisting that choice.
 *
 * The switcher swaps the same three things app.blade.php emits server-side —
 * the pack stylesheet <link>, the --pack-* palette variables, and the
 * data-theme attribute — so what renders here is what the pack renders
 * everywhere, not an approximation of it. Nothing is written back: the viewer's
 * own pack and the instance default are untouched, and a reload restores them.
 */
const props = defineProps({
    packs: { type: Array, default: () => [] },
});

const page = usePage();

// --- Pack switcher -------------------------------------------------------

const NO_PACK = "";

// Start on whatever the viewer is actually seeing, so the page opens looking
// like the rest of the site rather than jumping to an arbitrary pack.
const selectedName = ref(page.props.pack?.name ?? NO_PACK);

const selected = computed(
    () => props.packs.find((pack) => pack.name === selectedName.value) ?? null,
);

// Icons resolve through the same one function as everywhere else; the override
// is what makes them follow the switcher instead of the active pack. Without
// it the sprites on this page would silently stay on the viewer's own pack
// while every CSS texture around them changed.
const iconPack = computed(() =>
    selected.value ? { name: selected.value.name, version: null } : null,
);
const { packIcon } = useResourcePackIcon(iconPack);

/*
 * Default Vanilla ships one fixed scheme with no light/dark counterpart, which
 * is why the site hides the toggle for it (Instance::canToggleDarkMode). The
 * same rule has to hold here or the preview would offer a combination that can
 * never occur.
 */
const canToggleTheme = computed(() => !selected.value?.is_vanilla);
const darkMode = ref(page.props.dark_mode ?? false);

/*
 * Until the toggle is touched, follow each pack's own dark_mode flag — the same
 * hint Instance::resolveDarkMode() falls back to. Without this, opening a pack
 * drawn for the dark theme while the viewer's own pack is light renders the page
 * as dark chrome under light-theme text, which is unreadable and tells you
 * nothing about the pack. Once the toggle is used it wins, so both pairings stay
 * reachable.
 */
const themeTouched = ref(false);

watch(selectedName, () => {
    if (!themeTouched.value && selected.value) {
        darkMode.value = selected.value.dark_mode;
    }
});

const theme = computed(() => {
    if (selected.value?.is_vanilla) {
        return "runemanager-vanilla";
    }

    return darkMode.value ? "runemanager-dark" : "runemanager";
});

const LINK_ID = "styleguide-pack-css";
const VARS_ID = "styleguide-pack-vars";

/*
 * The document is shared with the layout around us, so every mutation here is
 * tracked and undone on unmount. Inertia keeps the same document across SPA
 * navigations — leaving a foreign pack's stylesheet attached would repaint the
 * whole site on the way out.
 */
const original = {
    theme: document.documentElement.dataset.theme,
    // The pack <link> the Blade root rendered, if any. Disabled rather than
    // removed so it can be restored byte-for-byte.
    link: document.querySelector('link[rel="stylesheet"][href*="resource-pack.css"]'),
    // The palette block Blade emits next to that <link>. It has no id, so it is
    // found by content. Left enabled it would leak the active pack's --pack-*
    // values into a preview of a pack that sets fewer of them — and into "No
    // resource pack", which must have none at all.
    vars: [...document.head.querySelectorAll("style")].find(
        (node) => node.textContent.includes("--pack-bg") || node.textContent.includes("--pack-accent"),
    ),
    // app.css gates the pack-derived surface colours on html.has-pack, so this
    // is the third thing that has to follow the switcher, not just the CSS.
    hasPack: document.documentElement.classList.contains("has-pack"),
};

const el = (id, tag) => {
    let node = document.getElementById(id);
    if (!node) {
        node = document.createElement(tag);
        node.id = id;
        document.head.appendChild(node);
    }

    return node;
};

const apply = () => {
    const pack = selected.value;

    if (original.link) {
        original.link.disabled = true;
    }
    if (original.vars) {
        original.vars.disabled = true;
    }

    const link = el(LINK_ID, "link");
    link.rel = "stylesheet";
    // A pack row whose files are missing has no stylesheet; render it as the
    // untextured interface rather than pointing <link> at a 404.
    if (pack?.css) {
        link.href = pack.css;
        link.disabled = false;
    } else {
        link.disabled = true;
    }

    // Mirrors the <style> block app.blade.php emits next to the <link>.
    el(VARS_ID, "style").textContent = pack
        ? `:root{${pack.background_color ? `--pack-bg:${pack.background_color};` : ""}${
            pack.accent_color ? `--pack-accent:${pack.accent_color};` : ""
        }${
            pack.accent_ui_color ? `--pack-accent-ui:${pack.accent_ui_color};` : ""
        }}`
        : "";

    document.documentElement.classList.toggle("has-pack", pack !== null);
    document.documentElement.classList.toggle("no-pack", pack === null);
    document.documentElement.dataset.theme = theme.value;
};

watch([selectedName, darkMode], apply, { immediate: true });

onBeforeUnmount(() => {
    document.getElementById(LINK_ID)?.remove();
    document.getElementById(VARS_ID)?.remove();
    if (original.link) {
        original.link.disabled = false;
    }
    if (original.vars) {
        original.vars.disabled = false;
    }
    document.documentElement.classList.toggle("has-pack", original.hasPack);
    document.documentElement.classList.toggle("no-pack", !original.hasPack);
    if (original.theme) {
        document.documentElement.dataset.theme = original.theme;
    }
});

// --- Sprite inventory ----------------------------------------------------

const inventoryOpen = ref(false);
const inventoryLoading = ref(false);
const inventory = ref(null);

/*
 * Fetched on demand, and re-fetched whenever the pack changes while the panel
 * is open. It answers the question that explains most pack differences: which
 * sprites this pack actually draws, and which are Default Vanilla's, copied in
 * at install time because the pack omitted them.
 *
 * A plain request, not router.reload() — see the route comment. An Inertia
 * visit would refresh the shared props and let DarkModeToggle restamp
 * <html data-theme>, throwing the preview back to the viewer's own pack.
 */
let inventoryRequest = 0;

const loadInventory = async () => {
    if (!inventoryOpen.value || !selected.value) {
        return;
    }

    const request = ++inventoryRequest;
    inventoryLoading.value = true;

    try {
        const response = await fetch(
            route("styleguide.sprites", { pack: selected.value.name }),
            { headers: { Accept: "application/json" } },
        );
        const data = await response.json();

        // Switching packs twice in quick succession can land the responses out
        // of order; only the newest one may write.
        if (request === inventoryRequest) {
            inventory.value = data;
        }
    } finally {
        if (request === inventoryRequest) {
            inventoryLoading.value = false;
        }
    }
};

watch([inventoryOpen, selectedName], loadInventory);

const borrowed = computed(
    () => inventory.value?.files.filter((file) => !file.own) ?? [],
);

// --- Sample data ---------------------------------------------------------

const BUTTON_VARIANTS = ["btn-primary", "btn-secondary", "btn-accent", "btn-neutral", "btn-info", "btn-success", "btn-warning", "btn-error", "btn-outline", "btn-ghost"];
const BUTTON_SIZES = ["btn-xs", "btn-sm", "", "btn-lg"];
const BADGE_VARIANTS = ["neutral", "primary", "secondary", "accent", "info", "success", "warning", "error"];
const BADGE_SIZES = ["xs", "sm", "", "lg"];
const SKILL_SLUGS = ["attack", "woodcutting", "fishing", "prayer", "runecraft", "overall"];
const EQUIPMENT_SLUGS = ["slot_head", "slot_cape", "slot_weapon", "slot_body", "slot_shield"];

const TABS = [
    { key: "one", label: "Inventory" },
    { key: "two", label: "Looting Bag" },
];

const checked = ref(true);
const text = ref("Zezima");
const choice = ref("b");
</script>

<template>
    <AppLayout title="Styleguide">
        <Head title="Styleguide" />

        <!-- The page header is one of the pack-coloured surfaces (.pack-bg-card,
             see AppLayout), and AppLayout only renders it when this slot is
             filled — so the styleguide has to fill it to show that surface. -->
        <template #header>
            <h1>Styleguide</h1>
        </template>

        <div class="mx-auto max-w-screen-xl space-y-8 p-4">
            <!-- Controls stay outside the textured surfaces so they remain
                 usable even if a pack renders a component unreadably. -->
            <div class="sticky top-0 z-30 rounded border border-base-300 bg-base-100 p-4 shadow">
                <div class="flex flex-wrap items-end gap-4">
                    <label class="flex flex-col gap-1">
                        <span class="text-xs uppercase tracking-wide opacity-70">Resource pack</span>
                        <select v-model="selectedName" class="select select-bordered select-sm min-w-56">
                            <option :value="NO_PACK">No resource pack</option>
                            <option v-for="pack in packs" :key="pack.name" :value="pack.name">
                                {{ pack.alias }}<template v-if="!pack.css"> (files missing)</template>
                            </option>
                        </select>
                    </label>

                    <label class="flex cursor-pointer items-center gap-2 pb-1"
                           :class="{ 'cursor-not-allowed opacity-50': !canToggleTheme }">
                        <input v-model="darkMode"
                               type="checkbox"
                               class="toggle toggle-sm"
                               :disabled="!canToggleTheme"
                               @change="themeTouched = true">
                        <span class="text-sm">Dark mode</span>
                    </label>

                    <label class="flex cursor-pointer items-center gap-2 pb-1">
                        <input v-model="inventoryOpen" type="checkbox" class="toggle toggle-sm">
                        <span class="text-sm">Sprite inventory</span>
                    </label>

                    <p class="pb-1 text-xs opacity-70">
                        Preview only — your own pack and the instance default are untouched.
                    </p>
                </div>

                <div v-if="inventoryOpen" class="mt-4 border-t border-base-300 pt-4">
                    <p v-if="!selected" class="text-sm opacity-70">
                        Select a pack to inspect which sprites it ships.
                    </p>
                    <div v-else-if="inventoryLoading || !inventory" class="space-y-2">
                        <div class="h-4 w-48 animate-pulse rounded bg-base-300"></div>
                        <div class="h-4 w-full animate-pulse rounded bg-base-300"></div>
                        <div class="h-4 w-3/4 animate-pulse rounded bg-base-300"></div>
                    </div>
                    <div v-else class="space-y-3">
                        <p class="text-sm">
                            <strong>{{ inventory.own }}</strong> sprite(s) shipped by this pack,
                            <strong>{{ inventory.borrowed }}</strong> borrowed from Default Vanilla.
                        </p>
                        <p v-if="borrowed.length" class="text-xs opacity-70">
                            Borrowed sprites are why a pack can put its own frame around someone
                            else's face. Listed below; everything else is the pack's own.
                        </p>
                        <div class="max-h-64 overflow-auto rounded border border-base-300">
                            <table class="table table-xs">
                                <thead>
                                    <tr><th>Sprite</th><th>Size</th><th>Source</th></tr>
                                </thead>
                                <tbody>
                                    <tr v-for="file in inventory.files" :key="file.path">
                                        <td class="font-mono text-xs">{{ file.path }}</td>
                                        <td class="text-xs opacity-70">{{ file.width }}×{{ file.height }}</td>
                                        <td class="text-xs">
                                            <span :class="file.own ? 'opacity-70' : 'text-warning'">
                                                {{ file.own ? 'own' : 'vanilla' }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!--
                Every section renders twice: once straight on the page and once
                inside a Card. Since textured controls leave their centre
                unpainted (see .btn in per-pack.css), "looks right on a card" no
                longer implies "looks right on the page" — the surface behind is
                part of what is being reviewed.
            -->
            <section v-for="surface in ['page', 'card']" :key="surface" class="space-y-6">
                <h2 class="text-lg font-semibold">
                    {{ surface === 'page' ? 'On the page background' : 'On a card surface' }}
                </h2>

                <component :is="surface === 'card' ? Card : 'div'" class="space-y-6">
                    <div class="space-y-2">
                        <h3 class="text-sm uppercase tracking-wide opacity-70">Buttons — shared components</h3>
                        <div class="flex flex-wrap items-center gap-2">
                            <PrimaryButton type="button">Primary</PrimaryButton>
                            <SecondaryButton>Secondary</SecondaryButton>
                            <DangerButton>Danger</DangerButton>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <h3 class="text-sm uppercase tracking-wide opacity-70">Buttons — every DaisyUI variant</h3>
                        <div class="flex flex-wrap items-center gap-2">
                            <button v-for="variant in BUTTON_VARIANTS" :key="variant"
                                    type="button" class="btn btn-sm" :class="variant">
                                {{ variant.replace('btn-', '') }}
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <h3 class="text-sm uppercase tracking-wide opacity-70">Buttons — sizes and states</h3>
                        <div class="flex flex-wrap items-center gap-2">
                            <button v-for="size in BUTTON_SIZES" :key="size || 'md'"
                                    type="button" class="btn" :class="size">
                                {{ size ? size.replace('btn-', '') : 'md' }}
                            </button>
                            <button type="button" class="btn btn-sm btn-active">active</button>
                            <button type="button" class="btn btn-sm" disabled>disabled</button>
                            <button type="button" class="btn btn-sm btn-circle">+</button>
                        </div>
                        <p class="text-xs opacity-60">Hover and focus each one — per-pack.css brightens rather than swapping sprites.</p>
                    </div>

                    <div class="space-y-2">
                        <h3 class="text-sm uppercase tracking-wide opacity-70">Badges</h3>
                        <div class="flex flex-wrap items-center gap-2">
                            <Badge v-for="variant in BADGE_VARIANTS" :key="variant" :variant="variant">
                                {{ variant }}
                            </Badge>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <Badge v-for="size in BADGE_SIZES" :key="size || 'md'" :size="size">
                                {{ size || 'md' }}
                            </Badge>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <h3 class="text-sm uppercase tracking-wide opacity-70">Form controls</h3>
                        <div class="flex flex-wrap items-end gap-3">
                            <div class="w-48"><TextInput v-model="text" /></div>
                            <div class="w-48"><TextInput v-model="text" error /></div>
                            <div class="w-48">
                                <SelectInput v-model="choice">
                                    <option value="a">First</option>
                                    <option value="b">Second</option>
                                </SelectInput>
                            </div>
                            <textarea class="textarea textarea-bordered w-48" rows="2">Notes</textarea>
                            <label class="flex items-center gap-2 pb-2">
                                <Checkbox v-model:checked="checked" />
                                <span class="text-sm">Checkbox</span>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <h3 class="text-sm uppercase tracking-wide opacity-70">Frames</h3>
                        <div class="grid gap-3 md:grid-cols-3">
                            <Card frame="border" padding="p-4">
                                <p class="text-sm">Card — <code>resource-pack-border</code></p>
                            </Card>
                            <Card frame="dialog" padding="p-4">
                                <p class="text-sm">Card — <code>resource-pack-dialog</code></p>
                            </Card>
                            <div class="resource-pack-iron-rivets rounded p-4">
                                <p class="text-sm"><code>resource-pack-iron-rivets</code></p>
                            </div>
                        </div>
                        <TabbedCard :tabs="TABS" body-class="p-4">
                            <template #one><p class="text-sm">First panel — <code>resource-pack-dialog-tabbed</code></p></template>
                            <template #two><p class="text-sm">Second panel</p></template>
                        </TabbedCard>
                    </div>

                    <div class="space-y-2">
                        <h3 class="text-sm uppercase tracking-wide opacity-70">Table in a frame</h3>
                        <div class="resource-pack-border rounded border pack-accent-border">
                            <table class="table table-zebra table-sm">
                                <thead><tr><th>#</th><th>Account</th><th>Total</th></tr></thead>
                                <tbody>
                                    <tr v-for="row in 3" :key="row">
                                        <td>{{ row }}</td>
                                        <td>Zezima</td>
                                        <td>2277</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <h3 class="text-sm uppercase tracking-wide opacity-70">Pack sprites via packIcon()</h3>
                        <div class="flex flex-wrap items-center gap-3">
                            <img v-for="slug in SKILL_SLUGS" :key="slug"
                                 v-bind="packIcon('skill', slug)" :alt="slug" class="h-6 w-6">
                            <img v-for="slug in EQUIPMENT_SLUGS" :key="slug"
                                 v-bind="packIcon('equipment', slug)" :alt="slug" class="h-8 w-8">
                        </div>
                        <p class="text-xs opacity-60">
                            A sprite the pack omits falls back to Default Vanilla — the inventory above says which.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <h3 class="text-sm uppercase tracking-wide opacity-70">Boxes and tabs</h3>
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="resource-pack-box p-3 text-sm">resource-pack-box</div>
                            <div class="resource-pack-panel p-3 text-sm">resource-pack-panel</div>
                            <a class="tab resource-pack-tab">tab</a>
                            <a class="tab resource-pack-tab tab-active">tab-active</a>
                        </div>
                    </div>
                </component>
            </section>
        </div>
    </AppLayout>
</template>
