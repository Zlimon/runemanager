<script setup>
import { computed, ref, watch } from 'vue';
import PaginationItem from '@/Components/Tables/PaginationItem.vue';

/*
 * Generic table built on DaisyUI's `table` styling with client-side sorting and
 * pagination (DaisyUI itself ships no JS for either). Columns are:
 *   { key, label, format?: 'number', sortable?: boolean, sort?: (row) => any,
 *     align?: 'left'|'right'|'center' }
 * Cells render row[key] (number-formatted when format==='number'); override any
 * cell with a `#cell-<key>` scoped slot exposing { row, value }.
 *
 * Pass `paginator` instead of `rows` when the server owns the ordering and the
 * page slice. The table then renders `paginator.data` untouched and its pager
 * navigates `meta.links` with Inertia. Sorting moves to the server with it: a
 * header is clickable only when `sortable` lists its key, and clicking emits
 * `sort` for the owner to act on rather than reordering the rows on screen —
 * which would look like a ranking of the board but only be one of the page.
 */
const props = defineProps({
    columns: { type: Array, required: true },
    rows: { type: Array, default: () => [] },
    perPage: { type: Number, default: 25 },
    // A resolved Laravel paginator: { data, links, meta: { current_page, last_page, links } }.
    paginator: { type: Object, default: null },
    // Props the paginator's links reload, for an Inertia partial visit.
    only: { type: Array, default: null },
    // Paginator mode: the column key the server sorted on, and its direction.
    sort: { type: String, default: null },
    direction: { type: String, default: 'asc' },
    // Paginator mode: the column keys the server will sort on. Nothing else clicks.
    sortable: { type: Array, default: null },
    // Key each row for v-for; string path or fn. Falls back to the index.
    rowKey: { type: [String, Function], default: null },
    // Optional per-row class (e.g. highlight the viewer's own row).
    rowClass: { type: Function, default: null },
    zebra: { type: Boolean, default: true },
});

const emit = defineEmits(['sort']);

const sortKey = ref(null);
const sortDir = ref('asc');
const currentPage = ref(1);

const sortAccessor = (row, column) => (column.sort ? column.sort(row) : row[column.key]);

const sourceRows = computed(() => (props.paginator ? props.paginator.data : props.rows));

const isSortable = (column) => (props.paginator
    ? Array.isArray(props.sortable) && props.sortable.includes(column.key)
    : column.sortable !== false);

// Which header carries the arrow — the server's answer, or this table's own.
const activeSort = computed(() => (props.paginator ? props.sort : sortKey.value));
const activeDirection = computed(() => (props.paginator ? props.direction : sortDir.value));

const sortedRows = computed(() => {
    if (!sortKey.value) {
        return sourceRows.value;
    }
    const column = props.columns.find((c) => c.key === sortKey.value);
    if (!column) {
        return sourceRows.value;
    }
    const dir = sortDir.value === 'asc' ? 1 : -1;
    return [...sourceRows.value].sort((a, b) => {
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
    if (props.paginator) {
        return sourceRows.value;
    }
    const start = (page.value - 1) * props.perPage;
    return sortedRows.value.slice(start, start + props.perPage);
});

// The server pager only earns its place once there is a second page.
const serverPages = computed(() => (props.paginator?.meta?.last_page ?? 1) > 1);

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
watch(sourceRows, () => { currentPage.value = 1; });

const toggleSort = (column) => {
    if (!isSortable(column)) {
        return;
    }
    if (props.paginator) {
        emit('sort', column.key);
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
        <div class="overflow-x-auto rounded pack-bg-card border pack-accent-border resource-pack-border">
            <table class="table" :class="{ 'table-zebra': zebra }">
                <thead>
                    <tr class="text-base-content/70">
                        <th v-for="column in columns" :key="column.key" :class="alignClass(column)">
                            <button v-if="isSortable(column)"
                                    type="button"
                                    class="inline-flex items-center gap-1 hover:text-base-content"
                                    @click="toggleSort(column)">
                                {{ column.label }}
                                <span v-if="activeSort === column.key" class="text-xs">
                                    {{ activeDirection === 'asc' ? '▲' : '▼' }}
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

        <nav v-if="paginator && serverPages" class="mt-4 flex items-center justify-between"
             aria-label="Pagination">
            <p class="text-xs text-base-content/60">
                Page {{ paginator.meta.current_page }} of {{ paginator.meta.last_page }}
            </p>
            <div class="join border border-base-300">
                <PaginationItem v-for="(link, index) in paginator.meta.links" :key="index"
                                :href="link.url"
                                :only="link.url && only ? only : undefined"
                                :active="link.active"
                                :disabled="!link.url">
                    <span v-html="link.label" />
                </PaginationItem>
            </div>
        </nav>

        <nav v-if="!paginator && totalPages > 1" class="mt-4 flex items-center justify-between" aria-label="Pagination">
            <p class="text-xs text-base-content/60">
                Page {{ page }} of {{ totalPages }}
            </p>
            <div class="join border border-base-300">
                <PaginationItem :disabled="page === 1" aria-label="Previous page" @click="goTo(page - 1)">
                    «
                </PaginationItem>
                <PaginationItem v-for="p in pageNumbers" :key="p" :active="p === page" @click="goTo(p)">
                    {{ p }}
                </PaginationItem>
                <PaginationItem :disabled="page === totalPages" aria-label="Next page" @click="goTo(page + 1)">
                    »
                </PaginationItem>
            </div>
        </nav>
    </div>
</template>
