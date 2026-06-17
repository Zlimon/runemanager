<script setup>
import { computed } from "vue";
import Freshness from "@/Components/Freshness.vue";

/*
 * Achievement Diary completion grid: 12 areas × 4 tiers. A filled cell means the
 * tier is complete. Driven by the {area: {tier: bool}} map pushed by the plugin;
 * renders in a fixed area/tier order regardless of key order.
 */
const props = defineProps({
    diaries: {
        type: Object,
        default: () => ({}),
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

const TIERS = ['Easy', 'Medium', 'Hard', 'Elite'];
const AREAS = [
    'Ardougne', 'Desert', 'Falador', 'Fremennik', 'Kandarin', 'Karamja',
    'Kourend', 'Lumbridge', 'Morytania', 'Varrock', 'Western', 'Wilderness',
];

const isDone = (area, tier) => props.diaries?.[area]?.[tier] === true;

const doneCount = (area) => TIERS.filter((tier) => isDone(area, tier)).length;

const completed = computed(() =>
    AREAS.reduce((total, area) => total + doneCount(area), 0));

const hasData = computed(() => completed.value > 0 || Object.keys(props.diaries ?? {}).length > 0);
</script>

<template>
    <div v-if="hasData">
        <div class="mb-2 flex items-baseline justify-between">
            <span class="text-xs text-base-content/60">{{ completed }} / 48 complete</span>
            <Freshness :updated-at="freshness" :stale-after-minutes="staleAfter" />
        </div>
        <ul class="space-y-1.5">
            <li v-for="area in AREAS" :key="area">
                <div class="flex items-baseline justify-between text-sm">
                    <span class="font-medium">{{ area }}</span>
                    <span class="text-xs text-base-content/60">{{ doneCount(area) }}/4</span>
                </div>
                <!-- One segment per tier (Easy → Elite); green when complete. -->
                <div class="mt-0.5 flex h-2 gap-px overflow-hidden rounded">
                    <span v-for="tier in TIERS" :key="tier" class="flex-1"
                          :class="isDone(area, tier) ? 'bg-success' : 'bg-base-300'"
                          :title="`${area} ${tier}: ${isDone(area, tier) ? 'complete' : 'incomplete'}`"></span>
                </div>
            </li>
        </ul>
    </div>
    <div v-else class="flex h-40 items-center justify-center text-center text-sm text-base-content/60">
        No achievement diary data yet.
    </div>
</template>
