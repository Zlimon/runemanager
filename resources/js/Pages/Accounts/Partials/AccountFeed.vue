<script setup>
import { onBeforeUnmount, onMounted, ref } from "vue";
import Card from "@/Components/Cards/Card.vue";
import FeedEventItem from "@/Components/Game/Feed/FeedEventItem.vue";

/*
 * This account's Activity feed. Seeds from the server-rendered list, then
 * prepends events from the public `feed` broadcast that belong to this
 * account. Unlike the Live Feed page, no admin display filters apply here —
 * every stored event shows (all level-ups, collection log slots, quests, and
 * whatever loot the plugin's own feed threshold let through).
 */
const props = defineProps({
    account: { type: Object, required: true },
    events: { type: Array, default: () => [] },
});

const MAX_EVENTS = 50;
const liveEvents = ref([...props.events]);

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

        <ul v-if="liveEvents.length" class="mt-2 space-y-2">
            <li v-for="event in liveEvents" :key="event.id">
                <FeedEventItem :event="event" @deleted="removeEvent" />
            </li>
        </ul>
        <Card v-else padding="p-6" class="mt-2 text-center text-sm text-base-content/60">
            No activity yet.
        </Card>
    </div>
</template>
