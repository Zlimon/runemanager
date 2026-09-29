<script setup>
import { computed, createApp, onMounted, onBeforeUnmount, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import L from "leaflet";
import AppLayout from "@/Layouts/AppLayout.vue";
import AccountCard from "@/Components/Cards/AccountCard.vue";
import EventPanel from "@/Pages/Map/Partials/EventPanel.vue";
import EventMarkerPopup from "@/Pages/Map/Partials/EventMarkerPopup.vue";
import { createOsrsMap, gameToLatLng } from "@/composables/useOsrsMap";
import { useResourcePackIcon } from "@/composables/useResourcePackIcon";

const props = defineProps({
    accounts: {
        type: Array,
        default: () => [],
    },
    focus: {
        type: Object,
        default: null, // { x, y, plane } to centre on (from a profile's minimap or an event)
    },
    // Accounts whose events the viewer may plot: [{ id, username, account_type, own, event_count }]
    // (event_count is null for other members' accounts).
    eventAccounts: {
        type: Array,
        default: () => [],
    },
    // The `?events=1,2` preselection, already narrowed to ids the viewer may see.
    eventAccountIds: {
        type: Array,
        default: () => [],
    },
});

// Drop a marker if no update arrives within this window (mirrors the server's
// map.visible_within_minutes); keeps stale players from lingering on the map.
const STALE_MS = 2 * 60 * 1000;

const mapEl = ref(null);
const onlineCount = ref(0);

let map = null;
let liveLayer = null;
const markers = new Map(); // username -> { marker, app, lastSeen }
let sweepTimer = null;

// Build the floating label: the same AccountCard box used on the Accounts index,
// mounted into a detached element so Leaflet can render it as the tooltip.
const buildCard = (account) => {
    const el = document.createElement("div");
    el.className = "map-card cursor-pointer";
    el.addEventListener("click", () => router.visit(route("accounts.show", account.username)));
    const app = createApp(AccountCard, { account });
    app.mount(el);
    return { el, app };
};

const toLatLng = (account) => gameToLatLng(account.x, account.y);

const COLOURS = {
    ironman: "#d1d5db",
    hardcore_ironman: "#ef4444",
    ultimate_ironman: "#facc15",
};
const colourFor = (type) => COLOURS[type] ?? "#38bdf8";

const upsert = (account) => {
    const latLng = toLatLng(account);
    const existing = markers.get(account.username);

    if (existing) {
        // Card content doesn't change as a player moves — just reposition the dot.
        existing.marker.setLatLng(latLng).setStyle({ fillColor: colourFor(account.account_type) });
        existing.lastSeen = Date.now();
    } else {
        // circleMarker is drawn exactly at the projected coordinate — no icon
        // anchor offset to get wrong. The AccountCard rides along as a tooltip.
        const marker = L.circleMarker(latLng, {
            radius: 5,
            color: "#000",
            weight: 1,
            fillColor: colourFor(account.account_type),
            fillOpacity: 1,
        }).addTo(liveLayer);

        const { el, app } = buildCard(account);
        marker.bindTooltip(el, {
            permanent: true,
            direction: "top",
            offset: [0, -6],
            interactive: true,
            className: "map-card-tip",
        });
        markers.set(account.username, { marker, app, account, lastSeen: Date.now() });
    }
    onlineCount.value = markers.size;
};

// Re-render a marker's card (the mounted AccountCard's props aren't reactive, so
// rebuild it) — used when the player's live activity changes.
const refreshCard = (entry) => {
    entry.app.unmount();
    const { el, app } = buildCard(entry.account);
    entry.app = app;
    entry.marker.setTooltipContent(el);
};

const removeMarker = (username, entry) => {
    liveLayer.removeLayer(entry.marker);
    entry.app.unmount();
    markers.delete(username);
};

const sweepStale = () => {
    const now = Date.now();
    for (const [username, entry] of markers) {
        if (now - entry.lastSeen > STALE_MS) {
            removeMarker(username, entry);
        }
    }
    onlineCount.value = markers.size;
};

/*
 * Event markers: where the selected accounts' feed events happened, one marker
 * per spot (the server groups them), in a layer of their own so the live
 * players can be hidden while browsing. The selection lives in the URL
 * (`?events=1,2&type=pet`) so a view can be shared.
 */
const MAX_EVENT_ACCOUNTS = 20;

// Distinct on the dark map tiles. An account's colour follows its place in the
// panel's list, so it stays put while other accounts are toggled.
const PALETTE = ["#f97316", "#22d3ee", "#a3e635", "#e879f9", "#facc15", "#60a5fa", "#f87171", "#34d399", "#c084fc", "#f5f5f5"];

const eventColours = computed(() => Object.fromEntries(
    props.eventAccounts.map((account, index) => [account.id, PALETTE[index % PALETTE.length]]),
));

// The server has already narrowed the preselection to ids the viewer may pick.
const selectedIds = ref(props.eventAccountIds.map(Number).slice(0, MAX_EVENT_ACCOUNTS));
const EVENT_TYPES = [
    { value: "", label: "All events" },
    { value: "level_up", label: "Level ups" },
    { value: "quest_complete", label: "Quests" },
    { value: "combat_achievement", label: "Combat tasks" },
    { value: "collection_log", label: "Collection log" },
    { value: "pet", label: "Pets" },
    { value: "death", label: "Deaths" },
    { value: "reward", label: "Rewards" },
    { value: "loot_drop", label: "Loot" },
];

// An unknown `?type=` (a stale or hand-edited link) means every type, rather
// than a request the server would reject.
const typeFromUrl = new URLSearchParams(window.location.search).get("type") ?? "";
const eventType = ref(EVENT_TYPES.some((option) => option.value === typeFromUrl) ? typeFromUrl : "");
const showLive = ref(true);

const eventMarkers = ref([]);
const eventsTruncated = ref(false);
const eventsLoading = ref(false);
const eventsFailed = ref(false);

const eventCount = computed(() => eventMarkers.value.reduce((sum, marker) => sum + marker.count, 0));

const { packIcon } = useResourcePackIcon();

// No picture (pets, unknown items) falls back to the trophy, as on the feed.
const TROPHY = { src: "/images/trophy.png" };
// Item pictures come once per response in `items` (item id -> image src), not
// inline on every event; other events carry their own { kind, slug } or { src }.
const eventItems = ref({});

const iconAttrs = (event) => {
    const itemIcon = event.item_id != null ? eventItems.value[event.item_id] : null;
    if (itemIcon) {
        return { src: itemIcon };
    }
    const icon = event.icon;
    if (!icon) {
        return TROPHY;
    }
    return icon.kind ? packIcon(icon.kind, icon.slug) : { src: icon.src };
};

let eventLayer = null;
let eventRequest = 0;

const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (c) => `&#${c.charCodeAt(0)};`);

// A dot in the account's colour (split between colours when several accounts
// share the spot), with the number of events when there is more than one.
const eventIcon = (marker) => {
    const colours = [...new Set(marker.events.map((event) => eventColours.value[event.account_id] ?? "#9ca3af"))];
    const fill = colours.length > 1
        ? `conic-gradient(${colours.map((colour, i) => `${colour} ${(i / colours.length) * 360}deg ${((i + 1) / colours.length) * 360}deg`).join(", ")})`
        : colours[0];
    const count = marker.count > 1 ? `<span class="map-event-count">${escapeHtml(marker.count > 99 ? "99+" : marker.count)}</span>` : "";

    return L.divIcon({
        className: "map-event-marker",
        html: `<span class="map-event-dot${marker.plane > 0 ? " map-event-dot--upstairs" : ""}" style="background: ${fill}"></span>${count}`,
        iconSize: [16, 16],
        iconAnchor: [8, 8],
        popupAnchor: [0, -8],
    });
};

// Popups are mounted when they open and unmounted when they close, so a few
// hundred markers don't each keep a Vue app alive.
const popupApps = new Map(); // Leaflet marker -> mounted popup app

const popupContent = (layer, marker) => {
    const el = document.createElement("div");
    const app = createApp(EventMarkerPopup, {
        marker: {
            ...marker,
            events: marker.events.map((event) => ({
                ...event,
                iconAttrs: iconAttrs(event),
                href: event.username ? route("accounts.show", event.username) : null,
                colour: eventColours.value[event.account_id],
            })),
        },
        navigate: (href) => router.visit(href),
    });
    app.mount(el);
    popupApps.get(layer)?.unmount();
    popupApps.set(layer, app);
    return el;
};

const unmountPopup = (layer) => {
    popupApps.get(layer)?.unmount();
    popupApps.delete(layer);
};

const drawEventMarkers = () => {
    map.closePopup();
    eventLayer.clearLayers();
    return eventMarkers.value.map((marker) => {
        const layer = L.marker(gameToLatLng(marker.x, marker.y), {
            icon: eventIcon(marker),
            title: marker.location || undefined,
            riseOnHover: true,
        });
        layer
            .bindPopup(() => popupContent(layer, marker), { className: "map-event-popup", maxWidth: 300 })
            .on("popupclose", () => unmountPopup(layer))
            .addTo(eventLayer);
        return { marker, layer };
    });
};

const fitToEvents = () => {
    if (!eventMarkers.value.length) {
        return;
    }
    const bounds = L.latLngBounds(eventMarkers.value.map((marker) => gameToLatLng(marker.x, marker.y)));
    if (eventMarkers.value.length === 1) {
        map.setView(bounds.getCenter(), 4);
    } else {
        map.fitBounds(bounds, { padding: [40, 40], maxZoom: 4 });
    }
};

/*
 * Fetch the markers for the current selection. `frame` is how the map settles
 * afterwards: 'fit' for a new set of accounts, 'focus' to open the marker at a
 * deep-linked spot (from an account's Recent Events), or nothing when only the
 * type changed.
 */
const loadEvents = async (frame = null) => {
    const request = ++eventRequest;
    eventsFailed.value = false;

    if (!selectedIds.value.length) {
        eventsLoading.value = false;
        eventMarkers.value = [];
        eventsTruncated.value = false;
        drawEventMarkers();
        return;
    }

    eventsLoading.value = true;
    try {
        const { data } = await window.axios.get(route("map.events"), {
            params: { accounts: selectedIds.value, type: eventType.value || undefined },
        });
        if (request !== eventRequest) {
            return;
        }
        eventItems.value = data.items ?? {};
        eventMarkers.value = data.markers ?? [];
        eventsTruncated.value = Boolean(data.truncated);
    } catch {
        if (request !== eventRequest) {
            return;
        }
        eventMarkers.value = [];
        eventsTruncated.value = false;
        eventsFailed.value = true;
    } finally {
        if (request === eventRequest) {
            eventsLoading.value = false;
        }
    }

    const drawn = drawEventMarkers();
    if (frame === "fit") {
        fitToEvents();
    } else if (frame === "focus" && props.focus) {
        const plane = props.focus.plane ?? 0;
        drawn.find(({ marker }) => marker.x === props.focus.x && marker.y === props.focus.y && marker.plane === plane)
            ?.layer.openPopup();
    }
};

let urlTimer = null;

// Replace (not push) the history entry, so toggling accounts doesn't fill the
// back button; debounced because browsers throttle replaceState.
const syncUrl = () => {
    window.clearTimeout(urlTimer);
    urlTimer = window.setTimeout(() => {
        const url = new URL(window.location.href);
        const setParam = (key, value) => (value ? url.searchParams.set(key, value) : url.searchParams.delete(key));
        setParam("events", selectedIds.value.join(","));
        setParam("type", eventType.value);
        router.replace({
            url: `${url.pathname}${url.search.replaceAll("%2C", ",")}`,
            preserveState: true,
            preserveScroll: true,
        });
    }, 300);
};

watch(selectedIds, () => {
    syncUrl();
    loadEvents("fit");
});

watch(eventType, () => {
    syncUrl();
    loadEvents();
});

watch(showLive, (visible) => (visible ? liveLayer.addTo(map) : liveLayer.remove()));

onMounted(() => {
    map = createOsrsMap(mapEl.value, { zoomControl: true });
    liveLayer = L.layerGroup().addTo(map);
    eventLayer = L.layerGroup().addTo(map);

    props.accounts.forEach(upsert);

    // Centre on the requested player or spot (deep-linked from a profile or an
    // event), else someone who's sharing, else Lumbridge. A preselected set of
    // accounts without a spot frames their events instead.
    const focus = props.focus ?? props.accounts[0] ?? { x: 3221, y: 3219 };
    map.setView(toLatLng(focus), props.focus ? 4 : 2);
    if (selectedIds.value.length) {
        loadEvents(props.focus ? "focus" : "fit");
    }

    window.Echo.private("map")
        .listen(".AccountMoved", (event) => upsert(event))
        .listen(".StatusUpdated", (event) => {
            const entry = markers.get(event.username);
            if (entry && (entry.account.activity !== event.activity
                || entry.account.activity_icon !== event.activity_icon
                || entry.account.world !== event.world)) {
                entry.account = {
                    ...entry.account,
                    activity: event.activity,
                    activity_icon: event.activity_icon,
                    world: event.world,
                };
                refreshCard(entry);
            }
        });

    sweepTimer = window.setInterval(sweepStale, 15000);
});

onBeforeUnmount(() => {
    if (sweepTimer) {
        window.clearInterval(sweepTimer);
    }
    window.clearTimeout(urlTimer);
    eventRequest++; // a response still in flight must not draw on the removed map
    window.Echo.leave("map");
    for (const app of popupApps.values()) {
        app.unmount();
    }
    popupApps.clear();
    for (const entry of markers.values()) {
        entry.app.unmount();
    }
    markers.clear();
    if (map) {
        map.remove();
    }
});
</script>

<template>
    <AppLayout title="Live Map">
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8">
                <div class="mb-3 flex items-baseline justify-between">
                    <h1 class="text-2xl font-bold text-base-content">Live Map</h1>
                    <span class="text-sm text-base-content/60">{{ onlineCount }} sharing</span>
                </div>
                <div class="grid gap-3 lg:grid-cols-[16rem_minmax(0,1fr)]">
                    <EventPanel v-model:selected="selectedIds" v-model:type="eventType" v-model:show-live="showLive"
                                :accounts="eventAccounts" :types="EVENT_TYPES" :colours="eventColours" :max-accounts="MAX_EVENT_ACCOUNTS"
                                :loading="eventsLoading" :failed="eventsFailed" :event-count="eventCount"
                                :place-count="eventMarkers.length" :truncated="eventsTruncated"
                                class="order-2 self-start lg:order-1" />
                    <div ref="mapEl" class="order-1 h-[75vh] w-full rounded border pack-accent-border resource-pack-border lg:order-2"></div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style>
/* Strip Leaflet's default tooltip chrome so only the AccountCard box shows. */
.map-card-tip.leaflet-tooltip {
    background: transparent;
    border: none;
    box-shadow: none;
    padding: 0;
    white-space: nowrap;
}
.map-card-tip.leaflet-tooltip::before {
    display: none;
}
/* The card is compact as a map label. */
.map-card-tip .h-16 {
    height: 2.5rem;
    width: 2.5rem;
}
.leaflet-container {
    background: #0b1722;
}
/* Event markers: a coloured dot per spot, a count badge when several events share it. */
.map-event-marker {
    position: relative;
}
.map-event-dot {
    display: block;
    width: 16px;
    height: 16px;
    border-radius: 9999px;
    border: 2px solid #000;
    box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.7);
}
/* Above the surface the plugin reported (plane > 0): a dashed ring. */
.map-event-dot--upstairs {
    border-style: dashed;
    border-color: #fff;
}
.map-event-count {
    position: absolute;
    top: -8px;
    right: -12px;
    min-width: 16px;
    padding: 0 3px;
    border-radius: 9999px;
    background: #000;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    line-height: 16px;
    text-align: center;
}
.map-event-popup .leaflet-popup-content-wrapper,
.map-event-popup .leaflet-popup-tip {
    background: var(--color-base-100);
    color: var(--color-base-content);
}
.map-event-popup .leaflet-popup-content {
    margin: 10px 12px;
}
.map-event-popup .leaflet-popup-content a {
    color: inherit;
}
.map-event-popup .leaflet-popup-content p {
    margin: 0;
}
</style>
