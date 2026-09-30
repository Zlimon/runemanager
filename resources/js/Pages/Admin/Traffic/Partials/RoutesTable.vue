<script setup>
import DataTable from '@/Components/Tables/DataTable.vue';
import { formatBytes, formatMs, formatPercent } from './format.js';

/*
 * Per-route volume, failure rate, latency and body size for the window,
 * sortable in the browser (the list is one row per plugin route).
 */
defineProps({
    // PluginTraffic::routes() rows.
    routes: { type: Array, required: true },
});

const columns = [
    { key: 'route', label: 'Route', sortable: true },
    { key: 'requests', label: 'Requests', format: 'number', align: 'right', sortable: true },
    { key: 'error_rate', label: 'Errors', align: 'right', sortable: true },
    { key: 'throttled', label: '429s', format: 'number', align: 'right', sortable: true },
    { key: 'avg_ms', label: 'Avg', align: 'right', sortable: true },
    // An upper bound from a latency histogram; null is past its last bucket.
    { key: 'p95_ms', label: 'p95', align: 'right', sortable: true, sort: (row) => row.p95_ms ?? Infinity },
    { key: 'avg_bytes', label: 'Avg size', align: 'right', sortable: true },
];
</script>

<template>
    <DataTable :columns="columns" :rows="routes" row-key="route">
        <template #cell-route="{ value }">
            <code class="text-xs">{{ value }}</code>
        </template>
        <template #cell-error_rate="{ row }">
            <span :class="row.error_rate >= 0.25 ? 'text-error font-semibold' : ''">
                {{ formatPercent(row.error_rate) }}
            </span>
        </template>
        <template #cell-avg_ms="{ value }">{{ formatMs(value) }}</template>
        <template #cell-p95_ms="{ value }">
            <span class="whitespace-nowrap">{{ value === null ? '> 10 s' : `≤ ${formatMs(value)}` }}</span>
        </template>
        <template #cell-avg_bytes="{ value }">{{ formatBytes(value) }}</template>
        <template #empty>No plugin requests in this window.</template>
    </DataTable>
</template>
