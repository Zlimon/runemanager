<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import DataTable from '@/Components/DataTable.vue';

/*
 * A DataTable preset for hiscore leaderboards: prepends the player Name column
 * (avatar + link to the profile, sorted by username) and highlights the
 * viewer's own accounts (SPEC §7.3). `columns` are the metric columns; each row
 * must carry an `account` (AccountResource) plus the metric keys.
 */
const props = defineProps({
    columns: { type: Array, required: true },
    rows: { type: Array, default: () => [] },
    perPage: { type: Number, default: 25 },
});

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
               :row-class="rowClass" :row-key="(row) => row.account.username">
        <template #cell-account="{ row }">
            <Link :href="route('accounts.show', row.account)"
                  class="flex items-center gap-3 whitespace-nowrap font-semibold text-base-content">
                <img :src="`data:image/jpeg;base64,${row.account.user?.icon ?? row.account.icon}`"
                     class="h-9 w-9 object-contain" alt="">
                {{ row.account.username }}
            </Link>
        </template>

        <template #empty>No hiscores found</template>
    </DataTable>
</template>
