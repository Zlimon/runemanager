<script setup>
import { computed } from "vue";
import { Deferred } from "@inertiajs/vue3";
import LineChart from "@/Components/Display/LineChart.vue";
import Loader from "@/Components/Display/Loader.vue";
import ChoiceChips from "@/Components/Tables/ChoiceChips.vue";

/*
 * One metric's history (the deferred `metricSeries` prop). Skills plot XP;
 * bosses and activities plot their count.
 */
const props = defineProps({
    // { metric, label, points: [{ t, v }] } once loaded.
    series: { type: Object, default: null },
    range: { type: String, required: true },
    // Skills chart XP; everything else a count.
    isSkill: { type: Boolean, default: true },
});

const emit = defineEmits(['update:range']);

const RANGES = [
    { value: 'week', label: 'Week' },
    { value: 'month', label: 'Month' },
    { value: 'year', label: 'Year' },
    { value: 'all', label: 'All' },
];

const chartSeries = computed(() => (props.series
    ? [{ label: props.series.label, points: props.series.points }]
    : []));
</script>

<template>
    <div>
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <h3>{{ series?.label ?? 'History' }}</h3>
            <ChoiceChips :model-value="range" :options="RANGES" label="Range"
                         @update:model-value="emit('update:range', $event)" />
        </div>

        <Deferred data="metricSeries">
            <template #fallback>
                <div class="flex h-60 items-center justify-center">
                    <Loader :component="true" :loading="true" />
                </div>
            </template>

            <template #default="{ reloading }">
                <div :class="{ 'opacity-50': reloading }">
                    <LineChart v-if="series?.points?.length" :series="chartSeries" :format="isSkill ? 'xp' : 'number'" />
                    <p v-else class="flex h-60 items-center justify-center text-sm text-base-content/60">
                        No history for {{ series?.label ?? 'this metric' }} yet.
                    </p>
                </div>
            </template>
        </Deferred>
    </div>
</template>
