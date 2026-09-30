<script setup>
import { computed } from "vue";
import dayjs from "dayjs";
import { Link, router } from "@inertiajs/vue3";
import { formatBytes } from "@/composables/useAdminFormatters.js";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Card from "@/Components/Cards/Card.vue";
import StatTile from "@/Components/Display/StatTile.vue";
import DataTable from "@/Components/Tables/DataTable.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import SecondaryButton from "@/Components/Buttons/SecondaryButton.vue";

const props = defineProps({
    stats: { type: Object, required: true },
    health: { type: Object, required: true },
    suspiciousUsers: { type: Array, default: () => [] },
    mode: { type: String, required: true },
    instanceName: { type: String, default: null },
});

const hasUserPage = route().has('admin.users.show');

const modeLabel = (mode) => mode.charAt(0).toUpperCase() + mode.slice(1);

// Each tile links to the admin page that owns the number it shows, so the
// overview doubles as a set of shortcuts on top of AdminNav.
const tiles = computed(() => [
    { label: 'Users', value: props.stats.users, href: route('admin.users') },
    { label: 'Suspended', value: props.stats.suspended_users, href: route('admin.users', { status: 'suspended' }) },
    { label: 'Accounts', value: props.stats.accounts, href: route('accounts.index') },
    { label: 'Screenshots', value: props.stats.screenshots, href: route('admin.screenshots') },
    { label: 'Hidden screenshots', value: props.stats.hidden_screenshots, href: route('admin.screenshots') },
    { label: 'News drafts', value: props.stats.news_drafts, href: route('admin.news') },
    { label: 'News scheduled', value: props.stats.news_scheduled, href: route('admin.news') },
    { label: 'Published news', value: props.stats.news_published, href: route('admin.news') },
    { label: 'Upcoming events', value: props.stats.upcoming_events, href: route('admin.calendar') },
    { label: 'Active announcements', value: props.stats.active_announcements, href: route('admin.announcements') },
    { label: 'Installed packs', value: props.stats.installed_packs, href: route('admin.packs') },
    { label: 'Requests (24h)', value: props.stats.requests_24h, href: route('admin.traffic') },
    { label: 'Flagged (24h)', value: props.stats.flagged_24h, href: route('admin.traffic') },
]);

const failedJobColumns = [
    { key: 'queue', label: 'Queue' },
    { key: 'message', label: 'Error' },
    { key: 'failed_at', label: 'Failed', sort: (row) => row.failed_at },
    { key: 'actions', label: '', sortable: false, align: 'right' },
];

const retryJob = (job) => router.post(route('admin.jobs.retry', job.uuid), {}, { preserveScroll: true });
const retryAll = () => router.post(route('admin.jobs.retry'), {}, { preserveScroll: true });
const forgetJob = (job) => router.delete(route('admin.jobs.forget', job.uuid), { preserveScroll: true });

const formatSync = (value) => (value ? dayjs(value).fromNow() : 'Never');
</script>

<template>
    <AdminLayout title="Admin">
        <div class="space-y-8">
            <Card>
                <p class="text-sm text-base-content/60">Instance mode</p>
                <p class="mt-1 text-lg font-semibold">
                    {{ modeLabel(mode) }}
                    <span v-if="instanceName" class="text-base-content/70">— {{ instanceName }}</span>
                </p>
            </Card>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                <template v-for="tile in tiles" :key="tile.label">
                    <Link v-if="tile.href" :href="tile.href" class="block transition hover:opacity-90">
                        <StatTile :label="tile.label" :value="tile.value" />
                    </Link>
                    <StatTile v-else :label="tile.label" :value="tile.value" />
                </template>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <Card>
                    <div class="flex items-baseline justify-between gap-2">
                        <h2 class="text-lg font-bold">Needs attention</h2>
                        <SecondaryButton v-if="health.failed_jobs.count > 0" type="button" class="btn-xs"
                                         @click="retryAll">
                            Retry all
                        </SecondaryButton>
                    </div>

                    <p v-if="health.failed_jobs.count === 0" class="mt-3 text-sm text-base-content/60">
                        No failed jobs — everything's healthy.
                    </p>

                    <template v-else>
                        <DataTable class="mt-3" :columns="failedJobColumns" :rows="health.failed_jobs.latest"
                                   row-key="uuid">
                            <template #cell-failed_at="{ value }">{{ formatSync(value) }}</template>
                            <template #cell-actions="{ row }">
                                <div class="flex justify-end gap-2">
                                    <PrimaryButton type="button" class="btn-xs" @click="retryJob(row)">
                                        Retry
                                    </PrimaryButton>
                                    <DangerButton type="button" class="btn-xs" @click="forgetJob(row)">
                                        Forget
                                    </DangerButton>
                                </div>
                            </template>
                        </DataTable>
                        <p v-if="health.failed_jobs.count > health.failed_jobs.latest.length"
                           class="mt-2 text-xs text-base-content/50">
                            Showing the {{ health.failed_jobs.latest.length }} most recent of
                            {{ health.failed_jobs.count }} failed jobs.
                        </p>
                    </template>

                    <div v-if="suspiciousUsers.length" class="mt-6">
                        <h3 class="text-sm font-semibold text-base-content/70">Suspicious traffic (24h)</h3>
                        <ul class="mt-2 space-y-3">
                            <li v-for="entry in suspiciousUsers" :key="entry.user_id ?? entry.ip" class="text-sm">
                                <div class="flex flex-wrap items-baseline justify-between gap-2">
                                    <template v-if="entry.user_id">
                                        <Link :href="route('admin.traffic.user', entry.user_id)"
                                              class="link link-hover font-medium">
                                            {{ entry.name ?? `User #${entry.user_id}` }}
                                        </Link>
                                        <Link v-if="hasUserPage" :href="route('admin.users.show', entry.user_id)"
                                              class="link text-xs">
                                            Manage user
                                        </Link>
                                    </template>
                                    <span v-else class="font-medium">Anonymous · <code>{{ entry.ip }}</code></span>
                                </div>
                                <ul class="mt-1 list-inside list-disc text-xs text-base-content/60">
                                    <li v-for="reason in entry.reasons" :key="reason">{{ reason }}</li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </Card>

                <Card>
                    <h2 class="text-lg font-bold">Health</h2>
                    <dl class="mt-3 space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-base-content/60">Pending queue jobs</dt>
                            <dd class="font-medium">
                                {{ health.pending_jobs.count ?? '—' }}
                                <span class="text-xs text-base-content/50">({{ health.pending_jobs.driver }})</span>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-base-content/60">Hiscores last synced</dt>
                            <dd class="font-medium">{{ formatSync(health.sync.hiscores_last_synced_at) }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-base-content/60">Collection log last synced</dt>
                            <dd class="font-medium">{{ formatSync(health.sync.collection_log_last_synced_at) }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-base-content/60">Screenshot storage</dt>
                            <dd class="font-medium">{{ formatBytes(health.storage.screenshots_bytes) }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-base-content/60">Resource pack storage</dt>
                            <dd class="font-medium">{{ formatBytes(health.storage.resource_packs_bytes) }}</dd>
                        </div>
                    </dl>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
