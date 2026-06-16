<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import FeedEventItem from "@/Components/Game/FeedEventItem.vue";
import { visibleFeedEvents } from "@/feed";

/*
 * This account's slice of the live feed. Seeds from the server-rendered list,
 * then prepends events from the public `feed` broadcast that belong to this
 * account. Level-ups are stored for every level; the configured milestones
 * filter the display.
 */
const props = defineProps({
    account: { type: Object, required: true },
    events: { type: Array, default: () => [] },
});

const MAX_EVENTS = 50;
const liveEvents = ref([...props.events]);

const milestones = computed(() => usePage().props.feed_level_milestones ?? []);
const visible = computed(() => visibleFeedEvents(liveEvents.value, milestones.value));

const removeEvent = (id) => {
    liveEvents.value = liveEvents.value.filter((e) => e.id !== id);
};

onMounted(() => {
    window.Echo.channel("feed").listen(".FeedEventCreated", (event) => {
        if (event.account?.username !== props.account.username) {
            return;
        }
        if (liveEvents.value.some((e) => e.id === event.id)) {
            return;
        }
        liveEvents.value = [event, ...liveEvents.value].slice(0, MAX_EVENTS);
    });
});

onBeforeUnmount(() => window.Echo.leave("feed"));
</script>

<template>
    <div>
        <div class="flex items-baseline justify-between">
            <h3>Activity</h3>
            <span class="flex items-center gap-1.5 text-xs text-base-content/60">
                <span class="inline-block h-1.5 w-1.5 rounded-full bg-success"></span>
                Live
            </span>
        </div>

        <ul v-if="visible.length" class="mt-2 space-y-2">
            <li v-for="event in visible" :key="event.id">
                <FeedEventItem :event="event" @deleted="removeEvent" />
            </li>
        </ul>
        <div v-else class="mt-2 rounded p-6 text-center text-sm text-base-content/60 pack-bg-card resource-pack-border">
            No activity yet.
        </div>
    </div>
</template>
