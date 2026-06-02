<script setup>
import { computed } from "vue";
import { Link, usePoll } from "@inertiajs/vue3";
import dayjs from "dayjs";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    events: {
        type: Array,
        default: () => [],
    },
    pollIntervalMs: {
        type: Number,
        default: 15_000,
    },
});

// SPEC §8.3 — first cut uses Inertia polling. usePoll auto-stops while the
// tab is hidden, so this is cheap. Migrate to broadcast when Reverb lands.
usePoll(props.pollIntervalMs, { only: ['events'], preserveScroll: true });

const formatValue = (gp) => {
    if (!gp) {
        return '';
    }
    if (gp >= 1_000_000) {
        return `${(gp / 1_000_000).toFixed(1)}M`;
    }
    if (gp >= 1_000) {
        return `${(gp / 1_000).toFixed(1)}K`;
    }
    return gp.toLocaleString('en-US');
};

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

const hasEvents = computed(() => props.events.length > 0);
</script>

<template>
    <AppLayout title="Live Feed">
        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="flex items-baseline justify-between">
                    <h1 class="text-2xl font-bold dark:text-white">Live Feed</h1>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Auto-refreshing every {{ Math.round(pollIntervalMs / 1000) }}s
                    </span>
                </div>

                <ul v-if="hasEvents" class="mt-4 space-y-2">
                    <li v-for="event in events" :key="event.id"
                        class="bg-base-100 border border-base-300 rounded p-3">
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
                            <span class="text-xs text-gray-500 dark:text-gray-400"
                                  :title="dayjs(event.occurred_at).format('MMM D, YYYY h:mm A')">
                                {{ dayjs(event.occurred_at).fromNow() }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                            {{ sentence(event) }}
                            <span v-if="event.type === 'loot_drop' && event.payload.total_value"
                                  class="ml-1 text-xs text-gray-500 dark:text-gray-400">
                                ({{ formatValue(event.payload.total_value) }} gp)
                            </span>
                        </p>
                    </li>
                </ul>

                <div v-else class="mt-4 bg-base-100 border border-base-300 rounded p-8 text-center text-gray-500 dark:text-gray-400">
                    No events yet — once the plugin starts pushing data, notable events will appear here.
                </div>
            </div>
        </div>
    </AppLayout>
</template>
