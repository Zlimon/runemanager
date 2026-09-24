<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "@/Components/Cards/Card.vue";
import FeedEventItem from "@/Components/Game/Feed/FeedEventItem.vue";
import { visibleFeedEvents } from "@/feed";

const props = defineProps({
    events: {
        type: Array,
        default: () => [],
    },
});

// Live over websockets. Seed from the server-rendered list, then
// prepend events broadcast on the public `feed` channel.
const events = ref([...props.events]);
const MAX_EVENTS = 100;

// Everything is stored; the Live Feed applies the admin's display filters
// (milestone level-ups, minimum loot value).
const milestones = computed(() => usePage().props.feed_level_milestones ?? []);
const minLootValue = computed(() => usePage().props.feed_loot_min_value ?? 0);
const visibleEvents = computed(() => visibleFeedEvents(events.value, milestones.value, minLootValue.value));

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
                    <h1 class="header-chatbox-sword text-2xl font-bold text-base-content">Live Feed</h1>
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

                <Card v-else padding="p-8" class="mt-4 text-center text-base-content/60">
                    No events yet — once the plugin starts pushing data, notable events will appear here.
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
