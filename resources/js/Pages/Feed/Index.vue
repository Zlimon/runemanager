<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FeedEventItem from "@/Components/Game/FeedEventItem.vue";
import { visibleFeedEvents } from "@/feed";

const props = defineProps({
    events: {
        type: Array,
        default: () => [],
    },
});

// SPEC §8.3 — live over websockets. Seed from the server-rendered list, then
// prepend events broadcast on the public `feed` channel.
const events = ref([...props.events]);
const MAX_EVENTS = 100;

// Every level-up is stored; the feed only shows the configured milestone levels.
const milestones = computed(() => usePage().props.feed_level_milestones ?? []);
const visibleEvents = computed(() => visibleFeedEvents(events.value, milestones.value));

onMounted(() => {
    window.Echo.channel("feed").listen(".FeedEventCreated", (event) => {
        if (events.value.some((e) => e.id === event.id)) {
            return;
        }
        events.value = [event, ...events.value].slice(0, MAX_EVENTS);
    });
});

onBeforeUnmount(() => window.Echo.leave("feed"));

const hasEvents = computed(() => visibleEvents.value.length > 0);

const removeEvent = (id) => {
    events.value = events.value.filter((e) => e.id !== id);
};
</script>

<template>
    <AppLayout title="Live Feed">
        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="flex items-baseline justify-between">
                    <h1 class="header-chatbox-sword text-2xl font-bold dark:text-white">Live Feed</h1>
                    <span class="flex items-center gap-1.5 text-xs text-base-content/60">
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-success"></span>
                        Live
                    </span>
                </div>

                <ul v-if="hasEvents" class="mt-4 space-y-2">
                    <li v-for="event in visibleEvents" :key="event.id">
                        <FeedEventItem :event="event" @deleted="removeEvent" />
                    </li>
                </ul>

                <div v-else class="mt-4 rounded p-8 text-center text-base-content/60 pack-bg-card resource-pack-border">
                    No events yet — once the plugin starts pushing data, notable events will appear here.
                </div>
            </div>
        </div>
    </AppLayout>
</template>
