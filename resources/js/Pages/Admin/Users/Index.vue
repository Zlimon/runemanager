<script setup>
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/Display/Badge.vue';
import ChoiceChips from '@/Components/Tables/ChoiceChips.vue';
import DataTable from '@/Components/Tables/DataTable.vue';
import Search from '@/Components/Form/Search.vue';
import { formatDate } from './Partials/formatDate.js';

const props = defineProps({
    // AdminUserResource collection: { data, links, meta }.
    users: { type: Object, required: true },
    filters: { type: Object, required: true },
    statuses: { type: Array, required: true },
});

const statusLabels = { all: 'All', active: 'Active', suspended: 'Suspended', owners: 'Owners' };
const statusOptions = props.statuses.map((value) => ({ value, label: statusLabels[value] ?? value }));

const form = ref({
    search: props.filters.search ?? '',
    status: props.filters.status ?? 'all',
});

const reload = debounce(() => {
    router.get(route('admin.users'), form.value, {
        only: ['users', 'filters'],
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 300);

watch(form, reload, { deep: true });

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'email', label: 'Email' },
    { key: 'verified', label: 'Verified', align: 'center' },
    { key: 'roles', label: 'Roles' },
    { key: 'accounts_count', label: 'Accounts', format: 'number', align: 'right' },
    { key: 'tokens_count', label: 'Tokens', format: 'number', align: 'right' },
    { key: 'last_token_used_at', label: 'Last token use' },
    { key: 'created_at', label: 'Joined' },
    { key: 'suspended', label: 'Status' },
];
</script>

<template>
    <AdminLayout title="Users">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <Search v-model="form.search" class="w-full max-w-sm" placeholder="Search name or email" />
                <ChoiceChips v-model="form.status" :options="statusOptions" label="Filter by status" />
            </div>

            <DataTable :columns="columns" :paginator="users" :only="['users', 'filters']" row-key="id">
                <template #cell-name="{ row }">
                    <Link :href="route('admin.users.show', row.id)" class="link link-hover font-medium">
                        {{ row.name }}
                    </Link>
                </template>
                <template #cell-verified="{ value }">
                    <span :class="value ? 'text-success' : 'text-base-content/40'">{{ value ? 'Yes' : 'No' }}</span>
                </template>
                <template #cell-roles="{ value }">
                    <div class="flex flex-wrap gap-1">
                        <Badge v-for="role in value" :key="role" size="sm"
                               :variant="role === 'Owner' ? 'primary' : 'neutral'">
                            {{ role }}
                        </Badge>
                    </div>
                </template>
                <template #cell-last_token_used_at="{ value }">
                    <span class="whitespace-nowrap text-base-content/70">{{ formatDate(value, 'Never') }}</span>
                </template>
                <template #cell-created_at="{ value }">
                    <span class="whitespace-nowrap text-base-content/70">{{ formatDate(value) }}</span>
                </template>
                <template #cell-suspended="{ value }">
                    <Badge size="sm" :variant="value ? 'error' : 'success'">
                        {{ value ? 'Suspended' : 'Active' }}
                    </Badge>
                </template>
                <template #empty>No users match.</template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
