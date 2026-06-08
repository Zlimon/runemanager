<script setup>
import { onMounted, onBeforeUnmount, ref } from "vue";
import L from "leaflet";
import { createOsrsMap, gameToLatLng } from "@/composables/useOsrsMap";

/*
 * A small, non-interactive OSRS map showing where an account is in-game. Seeded
 * with the account's last known position and updated live from the shared `map`
 * broadcast channel (filtered to this account) while it's sharing location.
 */
const props = defineProps({
    username: {
        type: String,
        required: true,
    },
    position: {
        type: Object,
        default: null, // { x, y, plane }
    },
});

const mapEl = ref(null);
let map = null;
let marker = null;

const place = (position) => {
    const latLng = gameToLatLng(position.x, position.y);
    if (marker) {
        marker.setLatLng(latLng);
    } else {
        marker = L.circleMarker(latLng, {
            radius: 5,
            color: "#000",
            weight: 1,
            fillColor: "#38bdf8",
            fillOpacity: 1,
        }).addTo(map);
    }
    map.setView(latLng, 3);
};

onMounted(() => {
    if (!props.position) {
        return;
    }

    // Overview only — disable every interaction.
    map = createOsrsMap(mapEl.value, {
        zoomControl: false,
        attributionControl: false,
        dragging: false,
        scrollWheelZoom: false,
        doubleClickZoom: false,
        boxZoom: false,
        keyboard: false,
        touchZoom: false,
    });
    place(props.position);

    window.Echo.private("map").listen(".AccountMoved", (event) => {
        if (event.username === props.username) {
            place({ x: event.x, y: event.y, plane: event.plane });
        }
    });
});

onBeforeUnmount(() => {
    window.Echo.leave("map");
    if (map) {
        map.remove();
    }
});
</script>

<template>
    <div class="h-48 w-48 overflow-hidden rounded resource-pack-border">
        <div v-if="position" ref="mapEl" class="h-full w-full"></div>
        <div v-else class="flex h-full items-center justify-center bg-base-200 p-3 text-center text-xs text-base-content/60">
            Location not shared
        </div>
    </div>
</template>
