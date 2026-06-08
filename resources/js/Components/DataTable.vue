<script setup>
import { computed, ref, watch } from 'vue';

/*
 * Generic table built on DaisyUI's `table` styling with client-side sorting and
 * pagination (DaisyUI itself ships no JS for either). Columns are:
 *   { key, label, format?: 'number', sortable?: boolean, sort?: (row) => any,
 *     align?: 'left'|'right'|'center' }
 * Cells render row[key] (number-formatted when format==='number'); override any
 * cell with a `#cell-<key>` scoped slot exposing { row, value }.
 */
const props = defineProps({
    columns: { type: Array, required: true },
    rows: { type: Array, default: () => [] },
    perPage: { type: Number, default: 25 },
    // Key each row for v-for; string path or fn. Falls back to the index.
    rowKey: { type: [String, Function], default: null },
    // Optional per-row class (e.g. highlight the viewer's own row).
    rowClass: { type: Function, default: null },
    zebra: { type: Boolean, default: true },
});

const sortKey = ref(null);
const sortDir = ref('asc');
const currentPage = ref(1);

const sortAccessor = (row, column) => (column.sort ? column.sort(row) : row[column.key]);

const sortedRows = computed(() => {
    if (!sortKey.value) {
        return props.rows;
    }
    const column = props.columns.find((c) => c.key === sortKey.value);
    if (!column) {
        return props.rows;
    }
    const dir = sortDir.value === 'asc' ? 1 : -1;
    return [...props.rows].sort((a, b) => {
        const av = sortAccessor(a, column);
        const bv = sortAccessor(b, column);
        if (av == null && bv == null) return 0;
        if (av == null) return 1;
        if (bv == null) return -1;
        if (column.format === 'number') return (Number(av) - Number(bv)) * dir;
        return String(av).localeCompare(String(bv)) * dir;
    });
});

const totalPages = computed(() => Math.max(1, Math.ceil(sortedRows.value.length / props.perPage)));
const page = computed(() => Math.min(currentPage.value, totalPages.value));

const pagedRows = computed(() => {
    const start = (page.value - 1) * props.perPage;
    return sortedRows.value.slice(start, start + props.perPage);
});

// Windowed page numbers (±2 around the current page).
const pageNumbers = computed(() => {
    const span = 2;
    const start = Math.max(1, page.value - span);
    const end = Math.min(totalPages.value, page.value + span);
    const numbers = [];
    for (let p = start; p <= end; p++) {
        numbers.push(p);
    }
    return numbers;
});

// Reset to the first page when the underlying data changes (e.g. search).
watch(() => props.rows, () => { currentPage.value = 1; });

const toggleSort = (column) => {
    if (column.sortable === false) {
        return;
    }
    if (sortKey.value === column.key) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = column.key;
        sortDir.value = 'asc';
    }
    currentPage.value = 1;
};

const goTo = (p) => { currentPage.value = Math.min(Math.max(1, p), totalPages.value); };

const keyFor = (row, index) => {
    if (typeof props.rowKey === 'function') return props.rowKey(row);
    if (props.rowKey) return row[props.rowKey];
    return index;
};

const alignClass = (column) => ({
    right: 'text-right',
    center: 'text-center',
}[column.align] ?? '');

const formatValue = (value, format) => {
    if (value === null || value === undefined) return '';
    if (format === 'number') return Number(value).toLocaleString('en-US');
    return value;
};
</script>

<template>
    <div>
        <div class="overflow-x-auto rounded pack-bg-card resource-pack-border">
            <table class="table" :class="{ 'table-zebra': zebra }">
                <thead>
                    <tr class="text-base-content/70">
                        <th v-for="column in columns" :key="column.key" :class="alignClass(column)">
                            <button v-if="column.sortable !== false"
                                    type="button"
                                    class="inline-flex items-center gap-1 hover:text-base-content"
                                    @click="toggleSort(column)">
                                {{ column.label }}
                                <span v-if="sortKey === column.key" class="text-xs">
                                    {{ sortDir === 'asc' ? '▲' : '▼' }}
                                </span>
                            </button>
                            <span v-else>{{ column.label }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="pagedRows.length === 0">
                        <td :colspan="columns.length" class="py-8 text-center text-base-content/60">
                            <slot name="empty">No results</slot>
                        </td>
                    </tr>
                    <tr v-for="(row, index) in pagedRows" :key="keyFor(row, index)"
                        :class="rowClass ? rowClass(row) : ''">
                        <td v-for="column in columns" :key="column.key" :class="alignClass(column)">
                            <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]">
                                {{ formatValue(row[column.key], column.format) }}
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="totalPages > 1" class="mt-4 flex items-center justify-between">
            <p class="text-xs text-base-content/60">
                Page {{ page }} of {{ totalPages }}
            </p>
            <div class="join">
                <button type="button" class="btn btn-sm join-item" :disabled="page === 1" @click="goTo(page - 1)">
                    «
                </button>
                <button v-for="p in pageNumbers" :key="p" type="button"
                        class="btn btn-sm join-item"
                        :class="{ 'btn-active': p === page }"
                        @click="goTo(p)">
                    {{ p }}
                </button>
                <button type="button" class="btn btn-sm join-item" :disabled="page === totalPages" @click="goTo(page + 1)">
                    »
                </button>
            </div>
        </div>
    </div>
</template>
