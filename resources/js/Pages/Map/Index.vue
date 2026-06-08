<script setup>
import { createApp, onMounted, onBeforeUnmount, ref } from "vue";
import { router } from "@inertiajs/vue3";
import L from "leaflet";
import AppLayout from "@/Layouts/AppLayout.vue";
import AccountCard from "@/Components/AccountCard.vue";
import { createOsrsMap, gameToLatLng } from "@/composables/useOsrsMap";

const props = defineProps({
    accounts: {
        type: Array,
        default: () => [],
    },
    focus: {
        type: Object,
        default: null, // { x, y } to centre on (from a profile's minimap)
    },
});

// Drop a marker if no update arrives within this window (mirrors the server's
// map.visible_within_minutes); keeps stale players from lingering on the map.
const STALE_MS = 2 * 60 * 1000;

const mapEl = ref(null);
const onlineCount = ref(0);

let map = null;
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
        }).addTo(map);

        const { el, app } = buildCard(account);
        marker.bindTooltip(el, {
            permanent: true,
            direction: "top",
            offset: [0, -6],
            interactive: true,
            className: "map-card-tip",
        });
        markers.set(account.username, { marker, app, lastSeen: Date.now() });
    }
    onlineCount.value = markers.size;
};

const removeMarker = (username, entry) => {
    map.removeLayer(entry.marker);
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

onMounted(() => {
    map = createOsrsMap(mapEl.value, { zoomControl: true });

    props.accounts.forEach(upsert);

    // Centre on the requested player (deep-linked from a profile), else someone
    // who's sharing, else Lumbridge.
    const focus = props.focus ?? props.accounts[0] ?? { x: 3221, y: 3219 };
    map.setView(toLatLng(focus), props.focus ? 4 : 2);

    window.Echo.private("map").listen(".AccountMoved", (event) => upsert(event));

    sweepTimer = window.setInterval(sweepStale, 15000);
});

onBeforeUnmount(() => {
    if (sweepTimer) {
        window.clearInterval(sweepTimer);
    }
    window.Echo.leave("map");
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
                    <h1 class="text-2xl font-bold dark:text-white">Live Map</h1>
                    <span class="text-sm text-base-content/60">{{ onlineCount }} sharing</span>
                </div>
                <div ref="mapEl" class="h-[75vh] w-full rounded resource-pack-border"></div>
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
</style>
