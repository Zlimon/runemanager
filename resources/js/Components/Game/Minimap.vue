<script setup>
import { onMounted, onBeforeUnmount, ref } from "vue";
import { Link } from "@inertiajs/vue3";
import L from "leaflet";
import { createOsrsMap, gameToLatLng } from "@/composables/useOsrsMap";

/*
 * A small, non-interactive OSRS map showing where an account is in-game. Seeded
 * with the account's last known position and updated live from the shared `map`
 * broadcast channel (filtered to this account) while it's sharing location.
 * Round and pack-themed like the in-game minimap; clickable through to the full
 * Live Map (centred on this account) when `href` is given.
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
    href: {
        type: String,
        default: null,
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
    <div class="relative h-48 w-48">
        <!-- Round frame, tinted by the active resource pack's accent colour. -->
        <div class="minimap-frame h-full w-full rounded-full p-[6px]">
            <div v-if="position" ref="mapEl" class="h-full w-full overflow-hidden rounded-full"></div>
            <div v-else
                 class="flex h-full w-full items-center justify-center rounded-full bg-base-200 p-3 text-center text-xs text-base-content/60">
                Location not shared
            </div>
        </div>

        <Link v-if="href && position" :href="href" title="Open the Live Map"
              class="absolute inset-0 z-[1000] rounded-full" />
    </div>
</template>

<style scoped>
.minimap-frame {
    background: var(--pack-accent, #94866d);
    box-shadow: inset 0 0 0 2px rgba(0, 0, 0, 0.45), 0 1px 3px rgba(0, 0, 0, 0.5);
}
</style>
