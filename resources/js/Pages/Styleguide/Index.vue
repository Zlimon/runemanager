<script setup>
import { computed, onBeforeUnmount, ref, watch } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import AuthenticationCardLogo from "@/Components/Auth/AuthenticationCardLogo.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import SecondaryButton from "@/Components/Buttons/SecondaryButton.vue";
import AccountCard from "@/Components/Cards/AccountCard.vue";
import Card from "@/Components/Cards/Card.vue";
import ClickableCard from "@/Components/Cards/ClickableCard.vue";
import NewsCard from "@/Components/Cards/NewsCard.vue";
import TabbedCard from "@/Components/Cards/TabbedCard.vue";
import Badge from "@/Components/Display/Badge.vue";
import LineChart from "@/Components/Display/LineChart.vue";
import Loader from "@/Components/Display/Loader.vue";
import StatTile from "@/Components/Display/StatTile.vue";
import ActionMessage from "@/Components/Form/ActionMessage.vue";
import ActionSection from "@/Components/Form/ActionSection.vue";
import Checkbox from "@/Components/Form/Checkbox.vue";
import FormSection from "@/Components/Form/FormSection.vue";
import InputError from "@/Components/Form/InputError.vue";
import InputLabel from "@/Components/Form/InputLabel.vue";
import Search from "@/Components/Form/Search.vue";
import SectionBorder from "@/Components/Form/SectionBorder.vue";
import SelectInput from "@/Components/Form/SelectInput.vue";
import TextInput from "@/Components/Form/TextInput.vue";
import FeedEventCard from "@/Components/Game/Feed/FeedEventCard.vue";
import FeedEventItem from "@/Components/Game/Feed/FeedEventItem.vue";
import ItemPicker from "@/Components/Game/Items/ItemPicker.vue";
import ItemSlot from "@/Components/Game/Items/ItemSlot.vue";
import ItemTooltip from "@/Components/Game/Items/ItemTooltip.vue";
import LootItems from "@/Components/Game/Items/LootItems.vue";
import ConfirmationModal from "@/Components/Modals/ConfirmationModal.vue";
import DialogModal from "@/Components/Modals/DialogModal.vue";
import ImageLightbox from "@/Components/Modals/ImageLightbox.vue";
import Modal from "@/Components/Modals/Modal.vue";
import ResourcePackConfirmModal from "@/Components/ResourcePacks/ResourcePackConfirmModal.vue";
import ResourcePackPicker from "@/Components/ResourcePacks/ResourcePackPicker.vue";
import ResourcePackTile from "@/Components/ResourcePacks/ResourcePackTile.vue";
import ChoiceChips from "@/Components/Tables/ChoiceChips.vue";
import DataTable from "@/Components/Tables/DataTable.vue";
import LeaderboardTable from "@/Components/Tables/LeaderboardTable.vue";
import SortChips from "@/Components/Tables/SortChips.vue";
import PaginationItem from "@/Components/Tables/PaginationItem.vue";
import { useResourcePackIcon } from "@/composables/useResourcePackIcon";

/*
 * Every shared component in one place, grouped as Components/ is, repainted as any installed pack
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
    // Real catalogue items (icon, examine, alch values) for the item samples;
    // empty when the item catalogue isn't loaded, which the samples survive.
    items: { type: Array, default: () => [] },
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
 * Vanilla ships one fixed scheme with no light/dark counterpart, which
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
 * sprites this pack actually draws, and which are Vanilla's, copied in
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
const unchecked = ref(false);
const text = ref("Zezima");
const choice = ref("b");
const query = ref("");
const saved = ref(true);
const activeTab = ref("two");
const pickedIcon = ref(null);
const pickedItem = ref(null);
const pickedPack = ref("");

// A 1×1 transparent GIF: account avatars are base64 blobs from the plugin, and
// the samples have none, so this keeps their <img> from drawing as broken.
const BLANK_ICON = "R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";

const hoursAgo = (hours) => new Date(Date.now() - hours * 3_600_000).toISOString();

// One quantity per sample item, covering the slot's M / K / plain formats.
const QUANTITIES = [25_000_000, 1, 12_500, 27, 1, 1];

const FALLBACK_ITEMS = ["Coins", "Abyssal whip", "Dragon bones", "Shark", "Twisted bow", "Rune platebody"]
    .map((name, index) => ({ _id: index + 1, name, examine: null, icon: null, highalch: null, lowalch: null }));

const sampleItems = computed(() => (props.items.length ? props.items : FALLBACK_ITEMS)
    .map((item, index) => ({
        id: item._id,
        name: item.name,
        icon: item.icon,
        examine: item.examine,
        highalch: item.highalch,
        lowalch: item.lowalch,
        quantity: QUANTITIES[index] ?? 1,
    })));

// The unstackable sample (the whip, when the catalogue is loaded) for the
// single-item states.
const featuredItem = computed(() => sampleItems.value[1] ?? sampleItems.value[0]);

const lootEvent = computed(() => ({
    id: 0,
    type: "loot_drop",
    account: { username: "Iron Lad", account_type: "ironman", user_id: null },
    payload: {
        source: "Vorkath",
        items: sampleItems.value.slice(0, 4),
        total_value: 3_450_000,
        position: { x: 2272, y: 4046, plane: 0, location: "Ungael" },
    },
    occurred_at: hoursAgo(2),
    screenshot_url: null,
}));

const LEVEL_EVENT = {
    id: 0,
    type: "level_up",
    account: { username: "Zezima", account_type: "normal", user_id: null },
    payload: { skill: "woodcutting", level: 99 },
    occurred_at: hoursAgo(26),
    screenshot_url: null,
};

const QUEST_EVENT = {
    id: 0,
    type: "quest_complete",
    account: { username: "Zezima", account_type: "normal", user_id: null },
    payload: { quest: "Dragon Slayer II" },
    occurred_at: hoursAgo(72),
    screenshot_url: "/images/newspost_default.png",
};

// The owner's view of a screenshot they hid from everyone else.
const HIDDEN_SHOT_EVENT = {
    ...QUEST_EVENT,
    id: 0,
    payload: { quest: "Monkey Madness II" },
    screenshot_hidden: true,
};

const NEWS_POSTS = [
    {
        id: 1,
        title: "Nex mass this Friday",
        slug: "nex-mass-this-friday",
        excerpt: "We are running a Nex mass on Friday at 19:00 game time. Bring your own brews and an ancient godsword if you have one.",
        image_url: "/images/newspost_default.png",
        published_at: hoursAgo(48),
        is_published: true,
        created_by: { id: 1, name: "Zezima" },
    },
    {
        id: 2,
        title: "Clan hiscores reset",
        slug: "clan-hiscores-reset",
        excerpt: "The seasonal boards are cleared on the first of the month.",
        image_url: "/images/newspost_default.png",
        published_at: null,
        is_published: false,
        created_by: { id: 1, name: "Zezima" },
    },
    {
        id: 3,
        title: "Summer event schedule",
        slug: "summer-event-schedule",
        excerpt: "Six weeks of events, one per weekend, with prizes from the clan coffer.",
        image_url: "/images/newspost_default.png",
        published_at: hoursAgo(-120),
        is_published: false,
        created_by: { id: 1, name: "Iron Lad" },
    },
];

const ACCOUNTS = [
    { username: "Zezima", account_type: "normal", level: 2277, online: true, activity: "Woodcutting", activity_icon: "woodcutting", world: 302, icon: null },
    { username: "Iron Lad", account_type: "ironman", level: 1850, online: false, icon: null },
    { username: "Hardcore Hal", account_type: "hardcore_ironman", level: 1432, online: true, world: 416, icon: null },
];

// A year of daily points, oldest first — the same shape WomAccountView::series()
// returns. `wiggle` gives each day a small deterministic up/down so the line
// doesn't read as a perfectly straight ramp, without pulling in a seeded RNG.
const daysAgo = (days) => Date.now() - days * 86_400_000;
const wiggle = (day, amplitude) => Math.sin(day * 0.9) * amplitude;

const yearSeries = (start, end, amplitude) => Array.from({ length: 365 }, (_, day) => {
    const progressed = start + ((end - start) * day) / 364;
    return { t: daysAgo(364 - day), v: Math.max(0, Math.round(progressed + wiggle(day, amplitude))) };
});

// An ironman's overall XP, ~800M growing to 832M over the year.
const XP_SERIES = [{ label: "Overall XP", points: yearSeries(800_000_000, 832_000_000, 400_000) }];

// EHP/EHB over the same year — two series, so the chart's legend shows.
const EHP_EHB_SERIES = [
    { label: "EHP", points: yearSeries(860, 912.8, 3) },
    { label: "EHB", points: yearSeries(48, 61.9, 1.5) },
];

// A week range — WomAccountView::series('week') — to check the x-axis
// clamps to the last real point instead of Chart.js's own tick rounding,
// and that a short span gets one tick a day rather than repeated labels.
const WEEK_SERIES = [{
    label: "Overall XP",
    points: Array.from({ length: 7 }, (_, day) => ({
        t: daysAgo(6 - day),
        v: 831_200_000 + day * 115_000 + Math.round(wiggle(day, 20_000)),
    })),
}];

const STAT_TILES = [
    { label: "Efficient hours played", value: 912.8, delta: 14.2, format: "hours" },
    { label: "Efficient hours bossed", value: 61.9, delta: -2.1, format: "hours" },
    { label: "Time to max", value: 867.4, delta: null, format: "hours" },
];

const TABLE_COLUMNS = [
    { key: "rank", label: "Rank", format: "number" },
    { key: "name", label: "Account" },
    { key: "total", label: "Total level", format: "number", align: "right" },
    { key: "note", label: "Note", sortable: false },
];

const TABLE_ROWS = Array.from({ length: 12 }, (_, index) => ({
    rank: index + 1,
    name: `Player ${index + 1}`,
    total: 2277 - index * 37,
    note: index === 0 ? "Maxed" : "",
}));

// The first row belongs to the viewer, so the own-account highlight shows.
const leaderboardRows = computed(() => ["Zezima", "Iron Lad", "Hardcore Hal", "Lynx Titan"]
    .map((username, index) => ({
        account: { username, user_id: index === 0 ? page.props.auth?.user?.id ?? null : null, icon: BLANK_ICON },
        level: 99 - index,
        xp: 13_034_431 * (4 - index),
    })));

const LEADERBOARD_COLUMNS = [
    { key: "level", label: "Level", format: "number", align: "right" },
    { key: "xp", label: "Experience", format: "number", align: "right" },
];

/*
 * A hiscore board's `filters`: the server sorted it on Level, descending, and
 * will sort on either metric. The Name column is absent from `sortable`, so it
 * stays a plain header while the two metrics are clickable.
 */
const LEADERBOARD_FILTERS = {
    username: "",
    per_page: 25,
    sort: "level",
    direction: "desc",
    sortable: ["level", "xp"],
};

// A search that matched nobody, so the empty state names the reason.
const LEADERBOARD_NO_MATCH = { username: "zezima" };

// The loot board's sort control: that board renders cards, so it has no headers.
const LOOT_SORT = {
    columns: ["rank", "total_value", "drops"],
    labels: { rank: "Rank", total_value: "Total value", drops: "Drops" },
    sort: "total_value",
    direction: "desc",
};

// A single-choice filter row, as /progress and the account WOM panel use for
// range/period/metric — clickable here, unlike SortChips' demo above.
const CHIP_RANGES = [
    { value: "week", label: "Week" },
    { value: "month", label: "Month" },
    { value: "year", label: "Year" },
    { value: "all", label: "All" },
];
const chipRange = ref("year");

/*
 * A hiscore board as the server sends one: one page of a longer list, plus the
 * pager described by the paginator's `meta.links`. The links point back at the
 * styleguide so the pager can be clicked without landing on a real board.
 */
const leaderboardPaginator = computed(() => ({
    data: leaderboardRows.value,
    meta: {
        current_page: 2,
        last_page: 3,
        per_page: 25,
        total: 54,
        links: [
            { url: route("styleguide"), label: "&laquo; Previous", active: false },
            { url: route("styleguide"), label: "1", active: false },
            { url: route("styleguide"), label: "2", active: true },
            { url: route("styleguide"), label: "3", active: false },
            { url: route("styleguide"), label: "Next &raquo;", active: false },
        ],
    },
}));

// --- Modals --------------------------------------------------------------

const openModal = ref(null);
const confirmAction = ref("switch");

const previewPack = computed(() => props.packs.find((pack) => !pack.is_vanilla) ?? props.packs[0] ?? null);

const showConfirm = (action) => {
    confirmAction.value = action;
    openModal.value = "pack";
};
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
                            <strong>{{ inventory.borrowed }}</strong> borrowed from Vanilla.
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
                Every category renders twice: once straight on the page and once
                inside a Card. Since textured controls leave their centre
                unpainted (see .btn in per-pack.css), "looks right on a card" no
                longer implies "looks right on the page" — the surface behind is
                part of what is being reviewed. Sections follow Components/.
            -->
            <section v-for="surface in ['page', 'card']" :key="surface" class="space-y-6">
                <h2 class="text-lg font-semibold">
                    {{ surface === 'page' ? 'On the page background' : 'On a card surface' }}
                </h2>

                <component :is="surface === 'card' ? Card : 'div'" class="space-y-10">
                    <!-- Buttons -->
                    <div class="space-y-4">
                        <h3 class="text-base font-semibold">Buttons</h3>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">Shared components</h4>
                            <div class="flex flex-wrap items-center gap-2">
                                <PrimaryButton type="button">Primary</PrimaryButton>
                                <SecondaryButton>Secondary</SecondaryButton>
                                <DangerButton>Danger</DangerButton>
                                <PrimaryButton type="button" disabled>Primary disabled</PrimaryButton>
                                <SecondaryButton disabled>Secondary disabled</SecondaryButton>
                                <DangerButton disabled>Danger disabled</DangerButton>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">Every DaisyUI variant</h4>
                            <div class="flex flex-wrap items-center gap-2">
                                <button v-for="variant in BUTTON_VARIANTS" :key="variant"
                                        type="button" class="btn btn-sm" :class="variant">
                                    {{ variant.replace('btn-', '') }}
                                </button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">Sizes and states</h4>
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
                    </div>

                    <!-- Display -->
                    <div class="space-y-4">
                        <h3 class="text-base font-semibold">Display</h3>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">Badge — variants and sizes</h4>
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
                            <h4 class="text-sm uppercase tracking-wide opacity-70">Loader — inline and bare</h4>
                            <div class="grid gap-3 md:grid-cols-2">
                                <Loader loading component>
                                    <p class="text-sm">Inline (<code>component</code>)</p>
                                </Loader>
                                <div class="rounded-lg p-6 pack-bg-card border pack-accent-border resource-pack-border">
                                    <Loader loading bare>
                                        <p class="text-sm">Bare, on a pack card — as the install modal draws it</p>
                                    </Loader>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">StatTile</h4>
                            <div class="grid gap-3 sm:grid-cols-3">
                                <StatTile v-for="tile in STAT_TILES" :key="tile.label"
                                          :label="tile.label" :value="tile.value" :delta="tile.delta"
                                          :format="tile.format" :icon="packIcon('skill', 'overall')" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">LineChart — one series (XP)</h4>
                            <LineChart :series="XP_SERIES" format="xp" :height="220" />
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">LineChart — two series (EHP / EHB)</h4>
                            <LineChart :series="EHP_EHB_SERIES" format="hours" :height="220" />
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">LineChart — week range (day ticks, clamped to the last point)</h4>
                            <LineChart :series="WEEK_SERIES" format="xp" :height="180" />
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">LineChart — empty state</h4>
                            <LineChart :series="[{ label: 'Overall XP', points: [] }]" format="xp" :height="160" />
                        </div>
                    </div>

                    <!-- Form -->
                    <div class="space-y-4">
                        <h3 class="text-base font-semibold">Form</h3>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">Fields — label, input, error</h4>
                            <div class="grid gap-4 md:grid-cols-3">
                                <div>
                                    <InputLabel value="Text input" />
                                    <TextInput v-model="text" class="w-full" />
                                </div>
                                <div>
                                    <InputLabel value="With an error" />
                                    <TextInput v-model="text" error class="w-full" />
                                    <InputError message="This username is already taken." />
                                </div>
                                <div>
                                    <InputLabel>Disabled (label via slot)</InputLabel>
                                    <TextInput v-model="text" disabled class="w-full" />
                                </div>
                                <div>
                                    <InputLabel value="Select" />
                                    <SelectInput v-model="choice" class="w-full">
                                        <option value="a">First</option>
                                        <option value="b">Second</option>
                                    </SelectInput>
                                </div>
                                <div>
                                    <InputLabel value="Select with an error" />
                                    <SelectInput v-model="choice" error class="w-full">
                                        <option value="a">First</option>
                                        <option value="b">Second</option>
                                    </SelectInput>
                                    <InputError :messages="['First problem.', 'Second problem.']" />
                                </div>
                                <div>
                                    <InputLabel value="Select disabled" />
                                    <SelectInput v-model="choice" disabled class="w-full">
                                        <option value="a">First</option>
                                        <option value="b">Second</option>
                                    </SelectInput>
                                </div>
                                <div>
                                    <InputLabel value="Search" />
                                    <Search v-model="query" placeholder="Search accounts…" />
                                </div>
                                <div>
                                    <InputLabel value="Search with an error" />
                                    <Search v-model="query" error placeholder="Search accounts…" />
                                </div>
                                <div>
                                    <InputLabel value="Search disabled" />
                                    <Search v-model="query" disabled placeholder="Search accounts…" />
                                </div>
                                <div>
                                    <InputLabel value="Textarea (raw DaisyUI)" />
                                    <textarea class="textarea textarea-bordered w-full" rows="2">Notes</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">Checkbox</h4>
                            <div class="flex flex-wrap items-center gap-6">
                                <label class="flex items-center gap-2">
                                    <Checkbox v-model:checked="checked" />
                                    <span class="text-sm">Checked</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <Checkbox v-model:checked="unchecked" />
                                    <span class="text-sm">Unchecked</span>
                                </label>
                                <label class="flex items-center gap-2 opacity-60">
                                    <Checkbox :checked="true" disabled />
                                    <span class="text-sm">Disabled</span>
                                </label>
                            </div>
                            <p class="text-xs opacity-60">
                                Tab to a checkbox for the focus ring. With no pack the checked fill is
                                the theme's primary; under a pack per-pack.css replaces the whole control
                                with the in-game sprites.
                            </p>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">FormSection, ActionMessage, SectionBorder, ActionSection</h4>
                            <FormSection>
                                <template #title>Form section</template>
                                <template #description>SectionTitle on the left, the form Card on the right with its actions bar.</template>
                                <template #form>
                                    <div class="col-span-6 sm:col-span-4">
                                        <InputLabel value="Display name" />
                                        <TextInput v-model="text" class="w-full" />
                                    </div>
                                </template>
                                <template #actions>
                                    <ActionMessage :on="saved" class="me-3">Saved.</ActionMessage>
                                    <PrimaryButton type="button" @click="saved = !saved">Toggle saved</PrimaryButton>
                                </template>
                            </FormSection>

                            <SectionBorder />

                            <ActionSection>
                                <template #title>Action section</template>
                                <template #description>A panel with its own controls rather than a submitted form.</template>
                                <template #content>
                                    <p class="text-sm">Two-factor, sessions and account deletion sit on this surface.</p>
                                    <DangerButton class="mt-4">Delete account</DangerButton>
                                </template>
                            </ActionSection>
                        </div>
                    </div>

                    <!-- Cards -->
                    <div class="space-y-4">
                        <h3 class="text-base font-semibold">Cards</h3>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">Card — frames</h4>
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
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">ClickableCard — hover, selected, disabled</h4>
                            <div class="grid gap-3 md:grid-cols-4">
                                <ClickableCard><p class="text-sm">Hover me</p></ClickableCard>
                                <ClickableCard selected><p class="text-sm">Selected</p></ClickableCard>
                                <ClickableCard disabled><p class="text-sm">Disabled</p></ClickableCard>
                                <ClickableCard frame="dialog"><p class="text-sm">Dialog frame</p></ClickableCard>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">TabbedCard — named slots and v-model</h4>
                            <div class="grid gap-3 md:grid-cols-2">
                                <TabbedCard :tabs="TABS" body-class="p-4">
                                    <template #one><p class="text-sm">First panel — <code>resource-pack-dialog-tabbed</code></p></template>
                                    <template #two><p class="text-sm">Second panel</p></template>
                                </TabbedCard>
                                <TabbedCard v-model="activeTab" :tabs="TABS" body-class="p-4">
                                    <template #default="{ active }">
                                        <p class="text-sm">One shared template; active tab: <code>{{ active }}</code></p>
                                    </template>
                                </TabbedCard>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">AccountCard — online, offline, account types</h4>
                            <div class="flex flex-wrap gap-3">
                                <AccountCard v-for="account in ACCOUNTS" :key="account.username" :account="account" />
                            </div>
                            <ClickableCard :surface="false" padding="" class="w-fit">
                                <AccountCard :account="ACCOUNTS[0]" />
                            </ClickableCard>
                            <p class="text-xs opacity-60">The last one is nested in a surface-less ClickableCard, as on Accounts.</p>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">NewsCard — published, draft, scheduled</h4>
                            <div class="grid gap-3 md:grid-cols-3">
                                <NewsCard v-for="post in NEWS_POSTS" :key="post.id" :post="post" />
                            </div>
                            <h4 class="text-sm uppercase tracking-wide opacity-70">NewsCard — compact, as on the dashboard</h4>
                            <div class="grid gap-3 md:grid-cols-2">
                                <NewsCard v-for="post in NEWS_POSTS.slice(0, 2)" :key="`compact-${post.id}`"
                                          :post="post" compact />
                            </div>
                        </div>
                    </div>

                    <!-- Tables -->
                    <div class="space-y-4">
                        <h3 class="text-base font-semibold">Tables</h3>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">DataTable — sortable, paginated</h4>
                            <DataTable :columns="TABLE_COLUMNS" :rows="TABLE_ROWS" :per-page="5" row-key="rank" />
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">DataTable — empty</h4>
                            <DataTable :columns="TABLE_COLUMNS" :rows="[]" />
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">LeaderboardTable — own account highlighted</h4>
                            <LeaderboardTable :columns="LEADERBOARD_COLUMNS" :rows="leaderboardRows" />
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">LeaderboardTable — server-paginated, server-sorted board</h4>
                            <LeaderboardTable :columns="LEADERBOARD_COLUMNS" :hiscores="leaderboardPaginator"
                                              :filters="LEADERBOARD_FILTERS" />
                            <p class="text-xs opacity-60">
                                How every hiscore board renders. The server owns the ranking, the sort and
                                the page slice: Level and Experience are clickable because the filters list
                                them, Level carries the active arrow, and Name does not sort. A metric
                                opens descending, as Level has here, so the best players come first; a
                                rank opens ascending, for the same reason. Here the clicks go nowhere
                                and the pager's links lead back to the styleguide.
                            </p>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">LeaderboardTable — search with no match</h4>
                            <LeaderboardTable :columns="LEADERBOARD_COLUMNS" :rows="[]"
                                              :filters="LEADERBOARD_NO_MATCH" />
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">SortChips — a card list's column headers</h4>
                            <SortChips :columns="LOOT_SORT.columns" :labels="LOOT_SORT.labels"
                                       :sort="LOOT_SORT.sort" :direction="LOOT_SORT.direction" />
                            <p class="text-xs opacity-60">
                                How the loot board sorts: it renders cards, so its headers become buttons
                                beside the search. The sorted column is filled in and carries the arrow.
                                Here the clicks go nowhere.
                            </p>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">ChoiceChips — a single-value filter (range/period/metric)</h4>
                            <ChoiceChips v-model="chipRange" :options="CHIP_RANGES" label="Range" />
                            <ChoiceChips v-model="chipRange" :options="CHIP_RANGES" label="Range (extra small)" size="xs" />
                            <p class="text-xs opacity-60">
                                Live — click a chip; the second row is size="xs", for narrow cards such as
                                the quest journal. Under a pack the selected chip carries both the
                                brightened btn-active face and an inset accent ring, since the pack strips
                                btn-primary's own fill.
                            </p>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">PaginationItem — active, idle, disabled</h4>
                            <div class="join">
                                <PaginationItem disabled aria-label="Previous">«</PaginationItem>
                                <PaginationItem active>1</PaginationItem>
                                <PaginationItem>2</PaginationItem>
                                <PaginationItem>3</PaginationItem>
                                <PaginationItem aria-label="Next">»</PaginationItem>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">Raw table in a frame</h4>
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
                    </div>

                    <!-- Resource packs -->
                    <div class="space-y-4">
                        <h3 class="text-base font-semibold">Resource packs</h3>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">ResourcePackTile — states</h4>
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5">
                                <ResourcePackTile alias="Static tile" fallback-text="None" />
                                <ResourcePackTile interactive alias="Interactive" fallback-text="Click" sublabel="(sublabel)" />
                                <ResourcePackTile interactive selected alias="Selected" fallback-text="On" />
                                <ResourcePackTile interactive disabled alias="Disabled" fallback-text="Off" />
                                <ResourcePackTile v-if="previewPack" interactive
                                                  :icon-url="previewPack.icon_url" :alias="previewPack.alias">
                                    <template #badge><Badge size="xs">Default</Badge></template>
                                </ResourcePackTile>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">ResourcePackPicker — every installed pack</h4>
                            <ResourcePackPicker v-model="pickedPack" :packs="packs"
                                                :default-id="previewPack?.id ?? null" allow-no-pack />
                            <p class="text-xs opacity-60">Selecting here only moves the tick; it applies nothing.</p>
                        </div>
                    </div>

                    <!-- Game: feed -->
                    <div class="space-y-4">
                        <h3 class="text-base font-semibold">Game — Feed</h3>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">FeedEventItem — as the Live Feed draws it</h4>
                            <div class="grid gap-3 md:grid-cols-2">
                                <FeedEventItem :event="lootEvent" />
                                <FeedEventItem :event="LEVEL_EVENT" />
                            </div>
                            <p class="text-xs opacity-60">
                                Sample events have no real row behind them, so the delete control (shown to
                                admins) only reaches a 404.
                            </p>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">FeedEventCard — screenshot, hidden screenshot (owner view), placeholder, plain</h4>
                            <div class="grid gap-3 md:grid-cols-4">
                                <FeedEventCard :event="QUEST_EVENT" />
                                <FeedEventCard :event="HIDDEN_SHOT_EVENT" />
                                <FeedEventCard :event="LEVEL_EVENT" placeholder />
                                <FeedEventCard :event="lootEvent" relative-date />
                            </div>
                        </div>
                    </div>

                    <!-- Game: items -->
                    <div class="space-y-4">
                        <h3 class="text-base font-semibold">Game — Items</h3>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">ItemSlot — quantities, dimmed, empty (hover for the tooltip)</h4>
                            <div class="flex flex-wrap items-center gap-2">
                                <ItemSlot v-for="item in sampleItems" :key="item.id"
                                          :icon="item.icon" :name="item.name" :quantity="item.quantity"
                                          :examine="item.examine" :highalch="item.highalch" :lowalch="item.lowalch" />
                                <ItemSlot :icon="featuredItem.icon" :name="featuredItem.name"
                                          subtext="Not yet obtained" dimmed />
                                <ItemSlot />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">ItemTooltip — pinned open</h4>
                            <div class="relative mt-24 h-12 w-12">
                                <ItemTooltip :name="featuredItem.name"
                                             :examine="featuredItem.examine ?? 'A weapon from the abyss.'"
                                             subtext="Obtained 3 times" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">LootItems — with total value</h4>
                            <LootItems :items="sampleItems" :total-value="3_450_000" />
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">ItemPicker — searches the catalogue</h4>
                            <div class="grid gap-4 md:grid-cols-2">
                                <ItemPicker v-model="pickedItem" />
                                <ItemPicker :model-value="null" error />
                                <ItemPicker v-model="pickedIcon" random />
                            </div>
                        </div>
                    </div>

                    <!-- Auth -->
                    <div class="space-y-4">
                        <h3 class="text-base font-semibold">Auth</h3>
                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">AuthenticationCardLogo — branding logo or default mark</h4>
                            <AuthenticationCardLogo />
                        </div>
                    </div>

                    <!-- Pack classes used directly in markup rather than through a component. -->
                    <div class="space-y-4">
                        <h3 class="text-base font-semibold">Pack classes</h3>

                        <!--
                            The row highlights only exist in a state you have to
                            provoke, so both the current row and a row to hover are
                            laid out side by side. The menu column reproduces
                            NavDropdown's framed panel, because those rows sit on the
                            pack's dialog parchment rather than on a flat colour —
                            which is what the active row's accent hairline is there
                            to survive.
                        -->
                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">Rows — current and hover</h4>
                            <div class="flex flex-wrap gap-6">
                                <ul class="w-56 space-y-1">
                                    <li>
                                        <a class="settings-nav-link is-active block rounded bg-base-300 px-3 py-2 text-sm font-semibold text-base-content">
                                            Settings row — current
                                        </a>
                                    </li>
                                    <li>
                                        <a class="settings-nav-link block rounded px-3 py-2 text-sm text-base-content/70 hover:bg-base-300/50">
                                            Settings row — hover me
                                        </a>
                                    </li>
                                </ul>

                                <div class="w-52 space-y-1 rounded border border-base-300 bg-base-200 p-3 text-base-content shadow-xl resource-pack-dialog">
                                    <a class="nav-menu-link is-active block rounded bg-base-300 px-3 py-2 font-semibold text-base-content">
                                        Menu row — current
                                    </a>
                                    <a class="nav-menu-link block rounded px-3 py-2 text-base-content hover:bg-base-300">
                                        Menu row — hover me
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">Boxes and tabs</h4>
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="resource-pack-box p-3 text-sm">resource-pack-box</div>
                                <div class="resource-pack-panel p-3 text-sm">resource-pack-panel</div>
                                <a class="tab resource-pack-tab">tab</a>
                                <a class="tab resource-pack-tab tab-active">tab-active</a>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm uppercase tracking-wide opacity-70">Pack sprites via packIcon()</h4>
                            <div class="flex flex-wrap items-center gap-3">
                                <img v-for="slug in SKILL_SLUGS" :key="slug"
                                     v-bind="packIcon('skill', slug)" :alt="slug" class="h-6 w-6">
                                <img v-for="slug in EQUIPMENT_SLUGS" :key="slug"
                                     v-bind="packIcon('equipment', slug)" :alt="slug" class="h-8 w-8">
                            </div>
                            <p class="text-xs opacity-60">
                                A sprite the pack omits falls back to Vanilla — the inventory above says
                                which. Components (AccountCard, FeedEventCard) resolve their own icons
                                against your active pack, not the switcher.
                            </p>
                        </div>
                    </div>
                </component>
            </section>

            <!--
                Overlays render once: they draw above the page, so the surface
                behind the trigger says nothing about them.
            -->
            <section class="space-y-4">
                <h2 class="text-lg font-semibold">Modals</h2>
                <div class="flex flex-wrap items-center gap-2">
                    <SecondaryButton @click="openModal = 'modal'">Modal</SecondaryButton>
                    <SecondaryButton @click="openModal = 'dialog'">DialogModal</SecondaryButton>
                    <SecondaryButton @click="openModal = 'confirmation'">ConfirmationModal</SecondaryButton>
                    <SecondaryButton :disabled="!previewPack" @click="showConfirm('switch')">
                        ResourcePackConfirmModal — switch
                    </SecondaryButton>
                    <SecondaryButton :disabled="!previewPack" @click="showConfirm('install')">
                        ResourcePackConfirmModal — install
                    </SecondaryButton>
                </div>

                <div class="space-y-2">
                    <h4 class="text-sm uppercase tracking-wide opacity-70">ImageLightbox — click the thumbnail</h4>
                    <ImageLightbox src="/images/newspost_default.png" alt="Sample screenshot" />
                </div>

                <Modal :show="openModal === 'modal'" max-width="md" @close="openModal = null">
                    <div class="p-6">
                        <p class="text-sm">The bare Modal: a framed dialog with whatever the slot holds.</p>
                        <SecondaryButton class="mt-4" @click="openModal = null">Close</SecondaryButton>
                    </div>
                </Modal>

                <DialogModal :show="openModal === 'dialog'" @close="openModal = null">
                    <template #title>Dialog modal</template>
                    <template #content>
                        <InputLabel value="A field inside a dialog" />
                        <TextInput v-model="text" class="w-full" />
                    </template>
                    <template #footer>
                        <SecondaryButton @click="openModal = null">Cancel</SecondaryButton>
                        <PrimaryButton type="button" class="ms-3" @click="openModal = null">Save</PrimaryButton>
                    </template>
                </DialogModal>

                <ConfirmationModal :show="openModal === 'confirmation'" @close="openModal = null">
                    <template #title>Delete this event?</template>
                    <template #content>The confirmation variant, with its warning icon.</template>
                    <template #footer>
                        <SecondaryButton @click="openModal = null">Cancel</SecondaryButton>
                        <DangerButton class="ms-3" @click="openModal = null">Delete</DangerButton>
                    </template>
                </ConfirmationModal>

                <ResourcePackConfirmModal :show="openModal === 'pack'"
                                          :pack="previewPack"
                                          :action="confirmAction"
                                          :currently-dark="darkMode"
                                          @confirm="openModal = null"
                                          @close="openModal = null" />
            </section>
        </div>
    </AppLayout>
</template>
