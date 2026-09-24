<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import DataTable from '@/Components/Tables/DataTable.vue';

/*
 * A DataTable preset for hiscore leaderboards: prepends the player Name column
 * (avatar + link to the profile, sorted by username) and highlights the
 * viewer's own accounts. `columns` are the metric columns; each row
 * must carry an `account` (AccountResource) plus the metric keys.
 *
 * A board pages, searches and sorts on the server: pass the page's `hiscores`
 * paginator and its `filters`, and listen for `sort` to run the visit. `rows`
 * is the unpaginated form, for a short list that needs none of that.
 */
const props = defineProps({
    columns: { type: Array, required: true },
    rows: { type: Array, default: () => [] },
    perPage: { type: Number, default: 25 },
    // A resolved Laravel paginator of leaderboard rows.
    hiscores: { type: Object, default: null },
    // The board's `filters` prop: { username, per_page, sort, direction, sortable }.
    filters: { type: Object, default: null },
});

// A column key the board wants sorted on; the page owns the visit that does it.
defineEmits(['sort']);

const filters = computed(() => props.filters ?? {});

const page = usePage();
const ownUserId = computed(() => page.props.auth?.user?.id ?? null);

const allColumns = computed(() => [
    { key: 'account', label: 'Name', sort: (row) => row.account.username },
    ...props.columns,
]);

const rowClass = (row) => (row.account.user_id === ownUserId.value ? 'bg-primary/15' : '');
</script>

<template>
    <DataTable :columns="allColumns" :rows="rows" :per-page="perPage"
               :paginator="hiscores" :only="['hiscores', 'filters']"
               :sort="filters.sort" :direction="filters.direction" :sortable="filters.sortable"
               :row-class="rowClass" :row-key="(row) => row.account.username"
               @sort="$emit('sort', $event)">
        <template #cell-account="{ row }">
            <Link :href="route('accounts.show', row.account)"
                  class="flex items-center gap-3 whitespace-nowrap font-semibold text-base-content">
                <img :src="`data:image/jpeg;base64,${row.account.icon}`"
                     class="h-9 w-9 object-contain" alt="">
                {{ row.account.username }}
            </Link>
        </template>

        <template #empty>
            {{ filters.username ? 'No accounts match this search' : 'No hiscores found' }}
        </template>
    </DataTable>
</template>
