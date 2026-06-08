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
    <!-- Sized to the OSRS fixed-mode minimap-and-compass frame (172x156). The
         round map disc sits in the frame's circular hole; the frame sprite (from
         the active pack, see per-pack.css) overlays on top. -->
    <div class="minimap">
        <div class="minimap__disc">
            <div v-if="position" ref="mapEl" class="h-full w-full"></div>
            <div v-else class="flex h-full w-full items-center justify-center bg-base-200 p-3 text-center text-xs text-base-content/60">
                Location not shared
            </div>
        </div>

        <div class="minimap__frame" aria-hidden="true"></div>

        <Link v-if="href && position" :href="href" title="Open the Live Map"
              class="absolute inset-0 z-[1000]" />
    </div>
</template>

<!-- Unscoped so per-pack.css can paint the frame sprite onto .minimap__frame. -->
<style>
.minimap {
    position: relative;
    width: 172px;
    height: 156px;
}
.minimap__disc {
    position: absolute;
    top: 6px;
    left: 22px;
    height: 148px;
    width: 148px;
    overflow: hidden;
    border-radius: 9999px;
    background: #0b1722;
    /* Pack-accent ring as the no-frame fallback; hidden under the frame sprite. */
    box-shadow: inset 0 0 0 2px var(--pack-accent, #6b6048);
}
.minimap__frame {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background-repeat: no-repeat;
    background-size: 172px 156px;
    /* background-image is supplied per-pack (fixed_mode/minimap_and_compass_frame.png). */
}
</style>
