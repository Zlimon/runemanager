<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { Link } from "@inertiajs/vue3";
import dayjs from "dayjs";
import AppLayout from "@/Layouts/AppLayout.vue";
import LootItems from "@/Components/Game/LootItems.vue";

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

onMounted(() => {
    window.Echo.channel("feed").listen(".FeedEventCreated", (event) => {
        if (events.value.some((e) => e.id === event.id)) {
            return;
        }
        events.value = [event, ...events.value].slice(0, MAX_EVENTS);
    });
});

onBeforeUnmount(() => window.Echo.leave("feed"));

const formatSkill = (slug) => slug.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());

const sentence = (event) => {
    const { type, payload } = event;
    switch (type) {
        case 'level_up':
            return `reached ${payload.milestone} ${formatSkill(payload.skill)} (now level ${payload.level})`;
        case 'loot_drop':
            return `received a drop from ${payload.source}`;
        case 'quest_complete':
            return `completed ${payload.quest}`;
        default:
            return type;
    }
};

const hasEvents = computed(() => events.value.length > 0);
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
                    <li v-for="event in events" :key="event.id"
                        class="rounded p-3 pack-bg-card resource-pack-border">
                        <div class="flex items-baseline justify-between">
                            <Link :href="route('accounts.show', { account: event.account.username })"
                                  class="flex items-center gap-2 font-semibold hover:underline">
                                <img v-if="event.account.account_type === 'ironman'"
                                     src="/images/ironman.png"
                                     class="h-5 w-5 object-contain"
                                     alt="">
                                <img v-else-if="event.account.account_type && event.account.account_type !== 'normal'"
                                     :src="`/images/${event.account.account_type}_ironman.png`"
                                     class="h-5 w-5 object-contain"
                                     alt="">
                                {{ event.account.username }}
                            </Link>
                            <span class="text-xs text-base-content/60"
                                  :title="dayjs(event.occurred_at).format('MMM D, YYYY h:mm A')">
                                {{ dayjs(event.occurred_at).fromNow() }}
                            </span>
                        </div>
                        <div class="mt-1 text-sm text-base-content/80">
                            <p>{{ sentence(event) }}</p>
                            <LootItems v-if="event.type === 'loot_drop'"
                                       :items="event.payload.items"
                                       :total-value="event.payload.total_value"
                                       class="mt-1.5" />
                        </div>
                    </li>
                </ul>

                <div v-else class="mt-4 rounded p-8 text-center text-base-content/60 pack-bg-card resource-pack-border">
                    No events yet — once the plugin starts pushing data, notable events will appear here.
                </div>
            </div>
        </div>
    </AppLayout>
</template>
