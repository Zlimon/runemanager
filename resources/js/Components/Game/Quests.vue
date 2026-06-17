<script setup>
import { computed } from "vue";
import Freshness from "@/Components/Freshness.vue";

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

const list = computed(() => props.quests?.quests ?? []);
const finished = computed(() => list.value.filter((q) => q[1] === 2).length);
</script>

<template>
    <div v-if="list.length">
        <div class="mb-2 flex items-baseline justify-between">
            <span class="text-xs text-base-content/60">{{ finished }} / {{ list.length }} complete</span>
            <Freshness :updated-at="freshness" :stale-after-minutes="staleAfter" />
        </div>
        <div class="max-h-60 overflow-y-auto pr-1 text-sm">
            <p v-for="quest in list" :key="quest[0]" :style="{ color: QUEST_STATUS_COLORS[quest[1]] }">
                {{ quest[0] }}
            </p>
        </div>
    </div>
    <div v-else class="flex h-40 items-center justify-center text-center text-sm text-base-content/60">
        No quest data yet.
    </div>
</template>
