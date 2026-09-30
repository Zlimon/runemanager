<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import Badge from '@/Components/Display/Badge.vue';
import DataTable from '@/Components/Tables/DataTable.vue';
import { flagLabels, flagVariant, formatBytes, formatMs, formatWhen } from './format.js';

/*
 * A server-paginated list of logged plugin requests (PluginTraffic::requests()),
 * newest first: who sent it, which account headers, the outcome and its flags.
 */
const props = defineProps({
    // Resource collection: { data, links, meta }.
    requests: { type: Object, required: true },
    // The props a page link reloads.
    only: { type: Array, required: true },
    // Hide the user column on the per-user page.
    showUser: { type: Boolean, default: true },
});

const columns = computed(() => [
    { key: 'created_at', label: 'When' },
    ...(props.showUser ? [{ key: 'user_name', label: 'User' }] : []),
    { key: 'username', label: 'Account' },
    { key: 'route', label: 'Route' },
    { key: 'status', label: 'Status', align: 'right' },
    { key: 'duration_ms', label: 'Time', align: 'right' },
    { key: 'request_bytes', label: 'Size', align: 'right' },
    { key: 'ip', label: 'IP' },
    { key: 'flags', label: 'Flags' },
]);

const statusClass = (status) => {
    if (status >= 500) return 'text-error font-semibold';
    if (status >= 400) return 'text-warning font-semibold';
    return 'text-success';
};
</script>

<template>
    <DataTable :columns="columns" :paginator="requests" :only="only" row-key="id">
        <template #cell-created_at="{ value }">
            <span class="whitespace-nowrap text-base-content/70">{{ formatWhen(value) }}</span>
        </template>
        <template #cell-user_name="{ row }">
            <Link v-if="row.user_id" :href="route('admin.traffic.user', row.user_id)" class="link link-hover">
                {{ row.user_name ?? `#${row.user_id}` }}
            </Link>
            <span v-else-if="row.login_email" class="text-base-content/70" title="Attempted login email">
                {{ row.login_email }}
            </span>
            <span v-else-if="row.unknown_login_email" class="text-base-content/70"
                  title="The login used an address no user has; only its hash is kept">
                Unknown email
            </span>
            <span v-else class="text-base-content/40">—</span>
        </template>
        <template #cell-username="{ row }">
            <span v-if="row.username" :title="row.account_hash">{{ row.username }}</span>
            <span v-else class="text-base-content/40">—</span>
        </template>
        <template #cell-route="{ row }">
            <code class="whitespace-nowrap text-xs">{{ row.method }} {{ row.route }}</code>
        </template>
        <template #cell-status="{ value }">
            <span :class="statusClass(value)">{{ value }}</span>
        </template>
        <template #cell-duration_ms="{ value }">
            <span class="whitespace-nowrap">{{ formatMs(value) }}</span>
        </template>
        <template #cell-request_bytes="{ value }">
            <span class="whitespace-nowrap">{{ formatBytes(value) }}</span>
        </template>
        <template #cell-ip="{ row }">
            <span class="whitespace-nowrap text-xs" :title="row.user_agent">{{ row.ip }}</span>
        </template>
        <template #cell-flags="{ value }">
            <div class="flex flex-wrap gap-1">
                <Badge v-for="flag in value" :key="flag" size="sm" :variant="flagVariant(flag)">
                    {{ flagLabels[flag] ?? flag }}
                </Badge>
            </div>
        </template>
        <template #empty>No matching requests in this window.</template>
    </DataTable>
</template>
