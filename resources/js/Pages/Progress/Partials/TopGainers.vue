<script setup>
import { ref } from 'vue';
import { Deferred, router } from '@inertiajs/vue3';
import Card from '@/Components/Cards/Card.vue';
import Loader from '@/Components/Display/Loader.vue';
import ChoiceChips from '@/Components/Tables/ChoiceChips.vue';
import AccountName from '@/Pages/Progress/Partials/AccountName.vue';

/*
 * The period's biggest gainers (the deferred `topGainers` prop). Period and
 * metric changes reload only that prop.
 */
const props = defineProps({
    gainers: { type: Array, default: null },
    period: { type: String, default: 'week' },
    metric: { type: String, default: 'exp' },
});

const PERIODS = [
    { value: 'day', label: 'Day' },
    { value: 'week', label: 'Week' },
    { value: 'month', label: 'Month' },
    { value: 'year', label: 'Year' },
];
const METRICS = [
    { value: 'exp', label: 'XP' },
    { value: 'ehp', label: 'EHP' },
    { value: 'ehb', label: 'EHB' },
];

const period = ref(props.period);
const metric = ref(props.metric);

const reload = () => router.reload({
    only: ['topGainers'],
    data: { period: period.value, metric: metric.value },
    preserveState: true,
    preserveScroll: true,
    replace: true,
});

const pickPeriod = (key) => { period.value = key; reload(); };
const pickMetric = (key) => { metric.value = key; reload(); };

const format = (value) => (metric.value === 'exp'
    ? Math.round(value).toLocaleString('en-US')
    : `${Number(value).toLocaleString('en-US', { maximumFractionDigits: 1 })} h`);
</script>

<template>
    <Card padding="p-4">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-xl font-bold">Top gainers</h2>
            <div class="flex flex-wrap gap-2">
                <ChoiceChips :model-value="metric" :options="METRICS" label="Metric" @update:model-value="pickMetric" />
                <ChoiceChips :model-value="period" :options="PERIODS" label="Period" @update:model-value="pickPeriod" />
            </div>
        </div>

        <Deferred data="topGainers">
            <template #fallback>
                <div class="flex h-40 items-center justify-center">
                    <Loader :component="true" :loading="true" />
                </div>
            </template>

            <template #default="{ reloading }">
                <ol v-if="gainers?.length" class="divide-y divide-base-content/10" :class="{ 'opacity-50': reloading }">
                    <li v-for="(row, index) in gainers" :key="row.account_id ?? `${index}-${row.username}`"
                        class="flex items-center gap-3 px-2 py-2">
                        <span class="w-6 text-center text-sm font-bold text-base-content/50">{{ index + 1 }}</span>
                        <AccountName class="flex-1" :username="row.username" :account-type="row.account_type"
                                     :account-id="row.account_id" />
                        <span class="text-sm font-semibold text-success">+{{ format(row.gained) }}</span>
                    </li>
                </ol>
                <p v-else class="py-8 text-center text-sm text-base-content/60">
                    Nobody gained anything in this period yet.
                </p>
            </template>
        </Deferred>
    </Card>
</template>
