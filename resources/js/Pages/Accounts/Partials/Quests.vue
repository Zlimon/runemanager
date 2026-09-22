<script setup>
import { computed, ref } from "vue";
import Freshness from "@/Pages/Accounts/Partials/Freshness.vue";
import Search from "@/Components/Form/Search.vue";

const props = defineProps({
    quests: {
        type: Object,
        default: null,
    },
    freshness: {
        type: String,
        default: null,
    },
    staleAfter: {
        type: Number,
        default: 60,
    },
});

// The plugin pushes a status code per quest (0 = not started, 1 = in progress,
// 2 = finished). Mirror the OSRS quest journal's red / yellow / green so the
// list reads the same as in-game.
const QUEST_STATUS_COLORS = {
    0: '#ff0000',
    1: '#ffff00',
    2: '#00ff00',
};

const STATUS_FILTERS = [
    { key: 'all', label: 'All', status: null },
    { key: 'completed', label: 'Completed', status: 2 },
    { key: 'started', label: 'Started', status: 1 },
    { key: 'not-started', label: 'Not started', status: 0 },
];

const activeFilter = ref('all');
const search = ref('');

const list = computed(() => props.quests?.quests ?? []);
const finished = computed(() => list.value.filter((q) => q[1] === 2).length);

const visible = computed(() => {
    const status = STATUS_FILTERS.find((f) => f.key === activeFilter.value)?.status ?? null;
    const needle = search.value.trim().toLowerCase();

    return list.value.filter(
        (quest) => (status === null || quest[1] === status)
            && (needle === '' || quest[0].toLowerCase().includes(needle)),
    );
});
</script>

<template>
    <div v-if="list.length">
        <div class="mb-2 flex items-baseline justify-between">
            <span class="text-xs text-base-content/60">{{ finished }} / {{ list.length }} complete</span>
            <Freshness :updated-at="freshness" :stale-after-minutes="staleAfter" />
        </div>

        <div role="tablist" class="tabs tabs-boxed mb-2">
            <a v-for="filter in STATUS_FILTERS" :key="filter.key"
               role="tab" class="tab resource-pack-tab tab-xs sm:tab-sm"
               :class="{ 'tab-active': activeFilter === filter.key }"
               @click="activeFilter = filter.key">
                {{ filter.label }}
            </a>
        </div>

        <Search v-model="search" placeholder="Search quests" class="input-sm mb-2" />

        <div class="max-h-60 overflow-y-auto pr-1 text-sm">
            <p v-for="quest in visible" :key="quest[0]" :style="{ color: QUEST_STATUS_COLORS[quest[1]] }">
                {{ quest[0] }}
            </p>
            <p v-if="visible.length === 0" class="py-4 text-center text-base-content/60">
                No quests match
            </p>
        </div>
    </div>
    <div v-else class="flex h-40 items-center justify-center text-center text-sm text-base-content/60">
        No quest data yet.
    </div>
</template>
