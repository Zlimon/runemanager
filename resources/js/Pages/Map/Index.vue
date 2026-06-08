<script setup>
import { onMounted, onBeforeUnmount, ref } from "vue";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    accounts: {
        type: Array,
        default: () => [],
    },
});

// OSRS map ↔ Leaflet transform (Explv's tiles + CRS.Simple). Constants and the
// game(x,y) → pixel mapping are the canonical Explv/RuneScape map values.
const MAP_HEIGHT_PX = 296704;
const RS_TILE_PX = 32;
const RS_OFFSET_X = 1152;
const RS_OFFSET_Y = 8328;
const TILES_URL = "https://raw.githubusercontent.com/Explv/osrs_map_tiles/master/0/{z}/{x}/{y}.png";

// Drop a marker if no update arrives within this window (mirrors the server's
// map.visible_within_minutes); keeps stale players from lingering on the map.
const STALE_MS = 2 * 60 * 1000;

const mapEl = ref(null);
const onlineCount = ref(0);

let map = null;
const markers = new Map(); // username -> { marker, lastSeen }
let sweepTimer = null;

const escapeHtml = (value) =>
    String(value).replace(/[&<>"]/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c]));

const toLatLng = (account) => {
    const px = (account.x - RS_OFFSET_X) * RS_TILE_PX + RS_TILE_PX / 4;
    const py = MAP_HEIGHT_PX - (account.y - RS_OFFSET_Y) * RS_TILE_PX - RS_TILE_PX / 4;
    return map.unproject(L.point(px, py), map.getMaxZoom());
};

const iconFor = (account) =>
    L.divIcon({
        className: "",
        html: `<div class="map-pin"><span class="map-pin-dot map-pin-${escapeHtml(account.account_type)}"></span><span class="map-pin-label">${escapeHtml(account.username)}</span></div>`,
        iconAnchor: [5, 5],
    });

const upsert = (account) => {
    const latLng = toLatLng(account);
    const existing = markers.get(account.username);

    if (existing) {
        existing.marker.setLatLng(latLng).setIcon(iconFor(account));
        existing.lastSeen = Date.now();
    } else {
        const marker = L.marker(latLng, { icon: iconFor(account) }).addTo(map);
        markers.set(account.username, { marker, lastSeen: Date.now() });
    }
    onlineCount.value = markers.size;
};

const sweepStale = () => {
    const now = Date.now();
    for (const [username, entry] of markers) {
        if (now - entry.lastSeen > STALE_MS) {
            map.removeLayer(entry.marker);
            markers.delete(username);
        }
    }
    onlineCount.value = markers.size;
};

onMounted(() => {
    map = L.map(mapEl.value, {
        crs: L.CRS.Simple,
        minZoom: 4,
        maxZoom: 11,
        zoomControl: true,
    });

    L.tileLayer(TILES_URL, {
        minZoom: 4,
        maxZoom: 11,
        noWrap: true,
        tms: true,
        attribution: 'Map data © <a href="https://oldschool.runescape.wiki">OSRS Wiki</a> / Explv',
    }).addTo(map);

    props.accounts.forEach(upsert);

    // Centre on someone if we have them, otherwise on Lumbridge.
    const focus = props.accounts[0] ?? { x: 3221, y: 3219 };
    map.setView(toLatLng(focus), 7);

    window.Echo.private("map").listen(".AccountMoved", (event) => upsert(event));

    sweepTimer = window.setInterval(sweepStale, 15000);
});

onBeforeUnmount(() => {
    if (sweepTimer) {
        window.clearInterval(sweepTimer);
    }
    window.Echo.leave("map");
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
.map-pin {
    display: flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
    transform: translate(-50%, -50%);
    width: max-content;
}
.map-pin-dot {
    display: inline-block;
    height: 10px;
    width: 10px;
    border-radius: 9999px;
    border: 1px solid rgba(0, 0, 0, 0.6);
    background: #38bdf8;
    box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.35);
}
.map-pin-ironman { background: #d1d5db; }
.map-pin-hardcore_ironman { background: #ef4444; }
.map-pin-ultimate_ironman { background: #facc15; }
.map-pin-label {
    font-size: 11px;
    font-weight: 700;
    color: #f0e6d2;
    text-shadow: 0 0 3px #000, 0 0 3px #000;
}
.leaflet-container {
    background: #0b1722;
}
</style>
