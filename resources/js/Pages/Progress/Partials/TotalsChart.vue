<script setup>
import { computed, ref } from 'vue';
import { Deferred, router } from '@inertiajs/vue3';
import Card from '@/Components/Cards/Card.vue';
import LineChart from '@/Components/Display/LineChart.vue';
import Loader from '@/Components/Display/Loader.vue';
import ChoiceChips from '@/Components/Tables/ChoiceChips.vue';

/*
 * The members' summed history (the deferred `series` prop). Changing the
 * range reloads only that prop; the XP / EHP-EHB switch is client-side.
 */
const props = defineProps({
    series: { type: Array, default: null },
    range: { type: String, default: 'year' },
});

const RANGES = [
    { value: 'week', label: 'Week' },
    { value: 'month', label: 'Month' },
    { value: 'year', label: 'Year' },
    { value: 'all', label: 'All' },
];
const METRICS = [
    { value: 'xp', label: 'XP' },
    { value: 'efficiency', label: 'EHP / EHB' },
];

// `series` values are the members' summed gain since the range start (the
// first point is ~0), not a running total, so the heading names the window
// they're measured over rather than implying an all-time figure.
const RANGE_SINCE = {
    week: 'since the start of the week',
    month: 'since the start of the month',
    year: 'since the start of the year',
    all: 'since tracking began',
};

const range = ref(props.range);
const metric = ref('xp');

const title = computed(() => `Combined progress ${RANGE_SINCE[range.value] ?? ''}`.trim());

const pickRange = (key) => {
    range.value = key;
    router.reload({ only: ['series'], data: { range: key }, preserveState: true, preserveScroll: true, replace: true });
};

const chartSeries = computed(() => {
    const points = props.series ?? [];

    if (metric.value === 'xp') {
        return [{ label: 'XP gained', points: points.map((p) => ({ t: p.t, v: p.exp })) }];
    }

    return [
        { label: 'EHP gained', points: points.map((p) => ({ t: p.t, v: p.ehp })) },
        { label: 'EHB gained', points: points.map((p) => ({ t: p.t, v: p.ehb })) },
    ];
});
</script>

<template>
    <Card padding="p-4">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-xl font-bold">{{ title }}</h2>
            <div class="flex flex-wrap gap-2">
                <ChoiceChips v-model="metric" :options="METRICS" label="Series" />
                <ChoiceChips :model-value="range" :options="RANGES" label="Range" @update:model-value="pickRange" />
            </div>
        </div>

        <Deferred data="series">
            <template #fallback>
                <div class="flex h-72 items-center justify-center">
                    <Loader :component="true" :loading="true" />
                </div>
            </template>

            <template #default="{ reloading }">
                <div :class="{ 'opacity-50': reloading }">
                    <LineChart :series="chartSeries" :format="metric === 'xp' ? 'xp' : 'hours'" :height="288" />
                </div>
            </template>
        </Deferred>
    </Card>
</template>
