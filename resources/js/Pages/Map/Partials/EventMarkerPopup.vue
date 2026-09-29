<script setup>
import { computed } from "vue";
import dayjs from "dayjs";

/*
 * The popup of one event marker: every event recorded at that spot, newest
 * first. It is mounted into a detached element for Leaflet, outside the page's
 * app, so the page hands it resolved icons and hrefs and a `navigate` callback
 * instead of it reaching for usePage() or route().
 */
const props = defineProps({
    // { x, y, plane, location, count, events: [{ id, username, title, occurred_at, iconAttrs, href|null, colour }] }
    marker: { type: Object, required: true },
    navigate: { type: Function, required: true },
});

const heading = computed(() => props.marker.location || `${props.marker.x}, ${props.marker.y}`);

// The map only draws the surface (plane 0); say when an event happened above it.
const planeLabel = computed(() => (props.marker.plane > 0 ? `upstairs (plane ${props.marker.plane})` : null));

const hidden = computed(() => props.marker.count - props.marker.events.length);

const fromNow = (date) => dayjs(date).fromNow();
const exact = (date) => dayjs(date).format("MMM D, YYYY h:mm A");
</script>

<template>
    <div class="w-64 text-base-content">
        <p class="font-semibold">{{ heading }}</p>
        <p class="mb-2 text-xs text-base-content/60">
            {{ marker.count }} {{ marker.count === 1 ? 'event' : 'events' }}<template v-if="planeLabel"> · {{ planeLabel }}</template>
        </p>
        <ul class="max-h-60 overflow-y-auto">
            <li v-for="event in marker.events" :key="event.id"
                class="flex items-start gap-2 border-b border-base-300 py-1.5 last:border-b-0">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center">
                    <img v-bind="event.iconAttrs" class="max-h-6 max-w-6 object-contain" alt="">
                </span>
                <span class="min-w-0 flex-1">
                    <component :is="event.href ? 'a' : 'span'" :href="event.href ?? undefined"
                               class="flex items-center gap-1 text-xs font-semibold"
                               :class="{ 'link link-hover': event.href }"
                               @click="event.href && ($event.preventDefault(), navigate(event.href))">
                        <span class="inline-block h-2 w-2 shrink-0 rounded-full border border-black/60"
                              :style="{ background: event.colour }" aria-hidden="true"></span>
                        {{ event.username ?? 'Unknown account' }}
                    </component>
                    <span class="block text-sm leading-snug">{{ event.title }}</span>
                    <span class="block text-xs text-base-content/60" :title="exact(event.occurred_at)">
                        {{ fromNow(event.occurred_at) }}
                    </span>
                </span>
            </li>
        </ul>
        <p v-if="hidden > 0" class="mt-1 text-xs text-base-content/60">and {{ hidden }} older here.</p>
    </div>
</template>
