<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import Card from '@/Components/Cards/Card.vue';
import ChoiceChips from '@/Components/Tables/ChoiceChips.vue';
import DataTable from '@/Components/Tables/DataTable.vue';
import SelectInput from '@/Components/Form/SelectInput.vue';
import StatTile from '@/Components/Display/StatTile.vue';
import RequestsChart from './Partials/RequestsChart.vue';
import RequestsFeed from './Partials/RequestsFeed.vue';
import RoutesTable from './Partials/RoutesTable.vue';
import { flagLabels, formatBytes, formatMs, formatPercent } from './Partials/format.js';

/*
 * The plugin API traffic monitor: what the RuneLite plugin sent in the chosen
 * window, which routes and users it came from, who looks abusive and why,
 * and the feed of requests that raised a flag.
 */
const props = defineProps({
    window: { type: String, required: true },
    windows: { type: Array, required: true },
    flags: { type: Array, required: true },
    filters: { type: Object, required: true },
    summary: { type: Object, required: true },
    timeline: { type: Array, required: true },
    routes: { type: Array, required: true },
    topUsers: { type: Array, required: true },
    suspicious: { type: Array, required: true },
    // Resource collection: { data, links, meta }.
    flagged: { type: Object, required: true },
});

const windowLabels = { '1h': 'Last hour', '24h': 'Last 24 hours', '7d': 'Last 7 days' };
const windowOptions = props.windows.map((value) => ({ value, label: windowLabels[value] ?? value }));

const form = ref({
    window: props.window,
    flag: props.filters.flag ?? '',
    route: props.filters.route ?? '',
    user: props.filters.user ?? '',
});

watch(form, () => {
    const query = Object.fromEntries(Object.entries(form.value).filter(([, value]) => value !== '' && value !== null));
    router.get(route('admin.traffic'), query, { preserveState: true, preserveScroll: true, replace: true });
}, { deep: true });

const hasUserPage = route().has('admin.users.show');

const topUserColumns = [
    { key: 'name', label: 'User' },
    { key: 'requests', label: 'Requests', format: 'number', align: 'right' },
    { key: 'bytes', label: 'Sent', align: 'right' },
    { key: 'error_rate', label: 'Errors', align: 'right' },
    { key: 'throttled', label: '429s', format: 'number', align: 'right' },
    { key: 'hashes', label: 'Hashes', format: 'number', align: 'right' },
];
</script>

<template>
    <SettingsLayout title="Plugin traffic">
        <template #actions>
            <ChoiceChips v-model="form.window" :options="windowOptions" label="Time window" />
        </template>

        <div class="space-y-8">
            <p class="text-sm text-base-content/70">
                Successful live pings (heartbeat, position, vitals, status) are not logged; their failures and flags are.
                Floods of rejected requests are sampled per IP, so those counts are minimums.
            </p>

            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <StatTile label="Requests" :value="summary.requests" />
                <StatTile label="Active users" :value="summary.users" />
                <StatTile label="Error rate" :value="formatPercent(summary.error_rate)" />
                <StatTile label="Throttled" :value="summary.throttled" />
                <StatTile label="Flagged" :value="summary.flagged" />
                <StatTile label="Data received" :value="formatBytes(summary.bytes)" />
                <StatTile label="Avg response" :value="formatMs(summary.avg_ms)" />
                <StatTile label="Suspicious" :value="suspicious.length" />
            </div>

            <Card>
                <h2 class="mb-3 text-lg font-semibold">Requests over time</h2>
                <RequestsChart :buckets="timeline" />
            </Card>

            <section>
                <h2 class="mb-3 text-lg font-semibold">Suspicious users</h2>
                <Card v-if="suspicious.length === 0" padding="p-4">
                    <p class="text-sm text-base-content/70">Nothing looks abusive in this window.</p>
                </Card>
                <div v-else class="grid gap-3 md:grid-cols-2">
                    <Card v-for="entry in suspicious" :key="entry.user_id ?? entry.ip" padding="p-4">
                        <div class="mb-2 flex flex-wrap items-baseline justify-between gap-2">
                            <template v-if="entry.user_id">
                                <Link :href="route('admin.traffic.user', entry.user_id)" class="link link-hover font-semibold">
                                    {{ entry.name ?? `User #${entry.user_id}` }}
                                </Link>
                                <Link v-if="hasUserPage" :href="route('admin.users.show', entry.user_id)" class="link text-sm">
                                    Manage user
                                </Link>
                            </template>
                            <span v-else class="font-semibold">Anonymous · <code>{{ entry.ip }}</code></span>
                        </div>
                        <ul class="list-inside list-disc text-sm text-base-content/80">
                            <li v-for="reason in entry.reasons" :key="reason">{{ reason }}</li>
                        </ul>
                    </Card>
                </div>
            </section>

            <section>
                <h2 class="mb-3 text-lg font-semibold">Routes</h2>
                <RoutesTable :routes="routes" />
            </section>

            <section>
                <h2 class="mb-3 text-lg font-semibold">Top users</h2>
                <DataTable :columns="topUserColumns" :rows="topUsers" row-key="user_id">
                    <template #cell-name="{ row }">
                        <Link :href="route('admin.traffic.user', row.user_id)" class="link link-hover font-medium">
                            {{ row.name ?? `User #${row.user_id}` }}
                        </Link>
                    </template>
                    <template #cell-bytes="{ value }">{{ formatBytes(value) }}</template>
                    <template #cell-error_rate="{ value }">
                        <span :class="value >= 0.25 ? 'text-error font-semibold' : ''">{{ formatPercent(value) }}</span>
                    </template>
                    <template #empty>No signed-in plugin traffic in this window.</template>
                </DataTable>
            </section>

            <section>
                <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
                    <h2 class="text-lg font-semibold">Flagged requests</h2>
                    <div class="flex flex-wrap gap-2">
                        <SelectInput v-model="form.flag" class="w-auto" aria-label="Filter by flag">
                            <option value="">Every flag</option>
                            <option v-for="flag in flags" :key="flag" :value="flag">{{ flagLabels[flag] ?? flag }}</option>
                        </SelectInput>
                        <SelectInput v-model="form.route" class="w-auto" aria-label="Filter by route">
                            <option value="">Every route</option>
                            <option v-for="row in routes" :key="row.route" :value="row.route">{{ row.route }}</option>
                        </SelectInput>
                        <button v-if="form.user" type="button" class="btn btn-sm btn-ghost" @click="form.user = ''">
                            User #{{ form.user }} ✕
                        </button>
                    </div>
                </div>
                <RequestsFeed :requests="flagged" :only="['flagged', 'filters']" />
            </section>
        </div>
    </SettingsLayout>
</template>
